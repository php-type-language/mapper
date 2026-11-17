<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Diagnostics\Path\Segment\KeySegment;
use TypeLang\Mapper\Diagnostics\Path\Segment\PropertySegment;
use TypeLang\Mapper\Diagnostics\Path\Segment\SegmentInterface;
use TypeLang\Mapper\Exception\InvalidValueException;
use TypeLang\Mapper\Exception\NotInterceptableExceptionInterface;
use TypeLang\Mapper\Type\ShapeType\Field;

/**
 * `array{a: int, b?: string}` and `object{...}`: the declared fields, each
 * by its type, an optional one may be absent. Without `...` the other keys
 * are dropped, with it they are kept and converted by the rest type.
 *
 * An array shape is an array in both directions. An object shape is a
 * {@see \stdClass} on denormalization, takes the public properties of any
 * object on normalization and gives an array or a {@see \stdClass} by the
 * configuration.
 *
 * @template-implements TypeInterface<array<array-key, mixed>|\stdClass>
 */
final class ShapeType implements TypeInterface
{
    /**
     * Declared keys, the other keys are the rest
     *
     * @var array<array-key, true>
     */
    private readonly array $keys;

    public function __construct(
        /**
         * @var list<Field>
         */
        public readonly array $fields,
        /**
         * Canonical description of the shape, for messages
         *
         * @var non-empty-string
         */
        public readonly string $name,
        /**
         * Type of the keys not declared, {@see null} in case of the shape is
         * sealed and drops them
         *
         * @var TypeInterface<mixed>|null
         */
        public readonly ?TypeInterface $rest = null,
        public readonly bool $isObject = false,
    ) {
        $keys = [];

        foreach ($fields as $field) {
            $keys[$field->key] = true;
        }

        $this->keys = $keys;
    }

    public function isDenormalizable(mixed $value, Context $context): bool
    {
        $input = $context->coercers->array->coerce($value);

        if ($input === null) {
            return false;
        }

        foreach ($this->fields as $field) {
            if (!\array_key_exists($field->key, $input)) {
                if ($field->isOptional) {
                    continue;
                }

                return false;
            }

            if (!$field->type->isDenormalizable($input[$field->key], $context)) {
                return false;
            }
        }

        if ($this->rest === null) {
            return !$context->config->shouldRejectExtraFields
                || \array_diff_key($input, $this->keys) === [];
        }

        foreach (\array_diff_key($input, $this->keys) as $item) {
            if (!$this->rest->isDenormalizable($item, $context)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<array-key, mixed>|\stdClass
     * @throws InvalidValueException in case of the value is not an array, a
     *         required field is absent or a field cannot be denormalized
     */
    public function denormalize(mixed $value, Context $context): array|\stdClass
    {
        $input = $context->coercers->array->coerce($value)
            ?? throw InvalidValueException::becauseInvalidType($this->name, $value);

        $result = $this->denormalizeFields($input, $context);

        if ($this->isObject) {
            return (object) $result;
        }

        return $result;
    }

    public function isNormalizable(mixed $value, Context $context): bool
    {
        $input = $this->read($value);

        if ($input === null) {
            return false;
        }

        foreach ($this->fields as $field) {
            if (!\array_key_exists($field->key, $input)) {
                if ($field->isOptional) {
                    continue;
                }

                return false;
            }

            if (!$field->type->isNormalizable($input[$field->key], $context)) {
                return false;
            }
        }

        if ($this->rest === null) {
            return true;
        }

        foreach (\array_diff_key($input, $this->keys) as $item) {
            if (!$this->rest->isNormalizable($item, $context)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<array-key, mixed>|\stdClass
     * @throws InvalidValueException in case of the value is not of the shape
     *         kind, a required field is absent or a field cannot be normalized
     */
    public function normalize(mixed $value, Context $context): array|\stdClass
    {
        $input = $this->read($value)
            ?? throw InvalidValueException::becauseInvalidType($this->name, $value);

        $result = $this->normalizeFields($input, $context);

        if ($this->isObject && !$context->config->isObjectAsArray) {
            return (object) $result;
        }

        return $result;
    }

    /**
     * @param array<array-key, mixed> $input
     * @return array<array-key, mixed>
     * @throws InvalidValueException in case of a required field is absent or
     *         a field cannot be denormalized
     */
    private function denormalizeFields(array $input, Context $context): array
    {
        $limit = $context->config->maxViolations;
        $result = [];
        $violations = [];

        foreach ($this->fields as $field) {
            $key = $field->key;

            if (!\array_key_exists($key, $input)) {
                if (!$field->isOptional) {
                    InvalidValueException::collect(
                        $violations,
                        InvalidValueException::becauseFieldIsMissing((string) $key, $this->createSegment($key)),
                        $limit,
                    );
                }

                continue;
            }

            try {
                $result[$key] = $field->type->denormalize($input[$key], $context);
            } catch (NotInterceptableExceptionInterface $e) {
                throw $e;
            } catch (\Exception $e) {
                InvalidValueException::collect(
                    $violations,
                    InvalidValueException::wrapWithPath($e, $this->createSegment($key), $input[$key]),
                    $limit,
                );
            }
        }

        if ($this->rest === null && $context->config->shouldRejectExtraFields) {
            foreach (\array_diff_key($input, $this->keys) as $key => $item) {
                InvalidValueException::collect(
                    $violations,
                    InvalidValueException::becauseFieldIsUnexpected((string) $key, $this->createSegment($key), $item),
                    $limit,
                );
            }
        }

        if ($this->rest !== null) {
            foreach (\array_diff_key($input, $this->keys) as $key => $item) {
                try {
                    $result[$key] = $this->rest->denormalize($item, $context);
                } catch (NotInterceptableExceptionInterface $e) {
                    throw $e;
                } catch (\Exception $e) {
                    InvalidValueException::collect(
                        $violations,
                        InvalidValueException::wrapWithPath($e, $this->createSegment($key), $item),
                        $limit,
                    );
                }
            }
        }

        if ($violations !== []) {
            throw InvalidValueException::becauseViolationsAreCollected($violations);
        }

        return $result;
    }

    /**
     * @param array<array-key, mixed> $input
     * @return array<array-key, mixed>
     * @throws InvalidValueException in case of a required field is absent or
     *         a field cannot be normalized
     */
    private function normalizeFields(array $input, Context $context): array
    {
        $result = [];

        foreach ($this->fields as $field) {
            $key = $field->key;

            if (!\array_key_exists($key, $input)) {
                if ($field->isOptional) {
                    continue;
                }

                throw InvalidValueException::becauseFieldIsMissing((string) $key, $this->createSegment($key));
            }

            try {
                $result[$key] = $field->type->normalize($input[$key], $context);
            } catch (NotInterceptableExceptionInterface $e) {
                throw $e;
            } catch (\Exception $e) {
                throw InvalidValueException::wrapWithPath($e, $this->createSegment($key), $input[$key]);
            }
        }

        if ($this->rest === null) {
            return $result;
        }

        foreach (\array_diff_key($input, $this->keys) as $key => $item) {
            try {
                $result[$key] = $this->rest->normalize($item, $context);
            } catch (NotInterceptableExceptionInterface $e) {
                throw $e;
            } catch (\Exception $e) {
                throw InvalidValueException::wrapWithPath($e, $this->createSegment($key), $item);
            }
        }

        return $result;
    }

    /**
     * Returns the fields of the value of the shape kind on normalization,
     * {@see null} in case of the value is of another kind.
     *
     * @return array<array-key, mixed>|null
     */
    private function read(mixed $value): ?array
    {
        if ($this->isObject) {
            if (\is_object($value)) {
                return \get_object_vars($value);
            }

            return null;
        }

        if (\is_array($value)) {
            return $value;
        }

        return null;
    }

    /**
     * Returns the property of an object shape, the key of an array shape or
     * of an empty name.
     */
    private function createSegment(int|string $key): SegmentInterface
    {
        $property = (string) $key;

        if ($this->isObject && $property !== '') {
            return new PropertySegment($property);
        }

        return new KeySegment($key);
    }
}
