<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Diagnostics\Path\Segment\KeySegment;
use TypeLang\Mapper\Diagnostics\Path\Segment\PropertySegment;
use TypeLang\Mapper\Exception\InvalidValueException;
use TypeLang\Mapper\Exception\NotInterceptableExceptionInterface;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Mapping\Metadata\AliasConflict;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Type\ClassType\Property;

/**
 * Object of a class: created and filled from an array or a
 * {@see \stdClass} on denormalization, turned into an array of its
 * properties on normalization. A {@see \stdClass} is accepted in the strict
 * context too.
 *
 * The type holds the plan only: the metadata and the types of the
 * properties of each direction. The instantiator of the class, the readers
 * and writers of the properties and the hooks of the class are taken from
 * {@see Context::$accessors} by the metadata. The hooks before a conversion
 * run in its check too.
 *
 * On denormalization a property takes its field by the external name or
 * by an alias, several of them present are resolved by the conflict of the
 * property.
 *
 * A property that cannot be read on normalization, such as an uninitialized
 * one, is a fault of the object: its {@see \Error} becomes a violation with
 * the path of the property. A property whose skip condition is matched by
 * its value is left out of the data and is not checked.
 *
 * @template T of object
 *
 * @template-implements TypeInterface<T>
 */
final class ClassType implements TypeInterface
{
    /**
     * Keys of the data the class takes, the other keys are extra
     *
     * @var array<array-key, true>
     */
    private readonly array $keys;

    public function __construct(
        /**
         * Description of the class the type is built from
         *
         * @var ClassMetadata<T>
         */
        public readonly ClassMetadata $metadata,
        /**
         * Properties filled on denormalization, with the types of the
         * written values
         *
         * @var list<Property<mixed>>
         */
        public readonly array $writable,
        /**
         * Properties taken on normalization, with the types of the read
         * values
         *
         * @var list<Property<mixed>>
         */
        public readonly array $readable,
        /**
         * Keys of the data the class takes besides its properties: the
         * fields of the tags of its bases with a discriminator
         *
         * @var list<string>
         */
        public readonly array $tagFields = [],
    ) {
        $keys = \array_fill_keys($tagFields, true);

        foreach ($writable as $property) {
            $keys[$property->metadata->externalName] = true;

            foreach ($property->metadata->aliases as $alias) {
                $keys[$alias] = true;
            }
        }

        $this->keys = $keys;
    }

    public function isDenormalizable(mixed $value, Context $context): bool
    {
        try {
            foreach ($context->accessors->get($this->metadata)->beforeDenormalization as $hook) {
                $value = $hook->beforeDenormalize($this->metadata, $value, $context);
            }
        } catch (NotInterceptableExceptionInterface $e) {
            throw $e;
        } catch (\Exception) {
            return false;
        }

        $value = $context->coercers->array->coerce($value);

        if ($value === null) {
            return false;
        }

        foreach ($this->writable as $property) {
            $key = $property->metadata->externalName;

            $present = self::getPresentNames($property->metadata, $value);

            if ($present !== []) {
                $key = self::selectName($property->metadata, $present, $value);
            }

            if ($key === null) {
                return false;
            }

            if (!\array_key_exists($key, $value)) {
                if ($property->metadata->default !== null) {
                    continue;
                }

                return false;
            }

            $isStrict = $property->metadata->write?->isStrict;
            $current = $isStrict === null ? $context : $context->withStrictTypes($isStrict);

            if (!$property->type->isDenormalizable($value[$key], $current)) {
                return false;
            }
        }

        return !$context->config->shouldRejectExtraFields
            || \array_diff_key($value, $this->keys) === [];
    }

    /**
     * @return T
     * @throws InvalidValueException in case of the value or one of its
     *         fields cannot be denormalized
     * @throws TypeDefinitionException in case of a strategy rejects the
     *         class or a property
     */
    public function denormalize(mixed $value, Context $context): object
    {
        $accessor = $context->accessors->get($this->metadata);
        $input = $value;

        try {
            foreach ($accessor->beforeDenormalization as $hook) {
                $value = $hook->beforeDenormalize($this->metadata, $value, $context);
            }

            $input = $value;
            $value = $context->coercers->array->coerce($input)
                ?? throw InvalidValueException::becauseInvalidType($this->metadata->name, $input)
                    ->withTemplate($this->metadata->typeErrorMessage);

            $object = $accessor->instantiator->instantiate($value);
        } catch (NotInterceptableExceptionInterface|InvalidValueException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw InvalidValueException::becauseValueIsRejected($e, $input);
        }

        $limit = $context->config->maxViolations;
        $violations = [];

        foreach ($this->writable as $property) {
            $name = $property->metadata->name;
            $key = $property->metadata->externalName;

            $present = self::getPresentNames($property->metadata, $value);

            if ($present !== []) {
                $key = self::selectName($property->metadata, $present, $value);
            }

            if ($key === null) {
                foreach (\array_slice($present, 1) as $duplicate) {
                    InvalidValueException::collect(
                        $violations,
                        InvalidValueException::becauseFieldIsDuplicated(
                            $duplicate,
                            $present[0],
                            new PropertySegment($duplicate),
                            $value[$duplicate],
                        ),
                        $limit,
                    );
                }

                continue;
            }

            if (!\array_key_exists($key, $value)) {
                $default = $property->metadata->default;

                if ($default === null) {
                    InvalidValueException::collect(
                        $violations,
                        InvalidValueException::becauseFieldIsMissing($key, new PropertySegment($key))
                            ->withTemplate($property->metadata->undefinedErrorMessage),
                        $limit,
                    );

                    continue;
                }

                $accessor->writers[$name]->write($object, $default->value);

                continue;
            }

            $field = $value[$key];
            $isStrict = $property->metadata->write?->isStrict;

            try {
                $accessor->writers[$name]->write($object, $property->type->denormalize(
                    $field,
                    $isStrict === null ? $context : $context->withStrictTypes($isStrict),
                ));
            } catch (NotInterceptableExceptionInterface $e) {
                throw $e;
            } catch (\Exception $e) {
                InvalidValueException::collect(
                    $violations,
                    InvalidValueException::wrapWithPath(
                        $e,
                        new PropertySegment($key),
                        $field,
                        $property->metadata->typeErrorMessage,
                    ),
                    $limit,
                );
            }
        }

        if ($context->config->shouldRejectExtraFields) {
            foreach (\array_diff_key($value, $this->keys) as $key => $field) {
                InvalidValueException::collect(
                    $violations,
                    InvalidValueException::becauseFieldIsUnexpected((string) $key, new KeySegment($key), $field),
                    $limit,
                );
            }
        }

        if ($violations !== []) {
            throw InvalidValueException::becauseViolationsAreCollected($violations);
        }

        try {
            foreach ($accessor->afterDenormalization as $hook) {
                $hook->afterDenormalize($this->metadata, $object, $input, $context);
            }
        } catch (NotInterceptableExceptionInterface|InvalidValueException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw InvalidValueException::becauseValueIsRejected($e, $input);
        }

        return $object;
    }

    /**
     * Checks the object as deep as {@see self::normalize()} does: each
     * property must be readable, such as initialized, and of its type.
     *
     * @throws TypeDefinitionException in case of a strategy rejects the
     *         class or a property
     */
    public function isNormalizable(mixed $value, Context $context): bool
    {
        if (!$value instanceof $this->metadata->name) {
            return false;
        }

        $accessor = $context->accessors->get($this->metadata);

        try {
            foreach ($accessor->beforeNormalization as $hook) {
                $value = $hook->beforeNormalize($this->metadata, $value, $context);
            }
        } catch (NotInterceptableExceptionInterface $e) {
            throw $e;
        } catch (\Exception) {
            return false;
        }

        foreach ($this->readable as $property) {
            try {
                $field = $accessor->readers[$property->metadata->name]->read($value);
            } catch (\Error) {
                return false;
            }

            if ($property->metadata->skip !== [] && self::isSkipped($property, $value, $field)) {
                continue;
            }

            if (!$property->type->isNormalizable($field, $context)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the values of the properties by their keys in the data, as
     * changed by the hooks: an array or a {@see \stdClass} by the class or
     * by the configuration.
     *
     * @return array<array-key, mixed>|\stdClass
     * @throws InvalidValueException in case of the value is not an object of
     *         the class or one of its properties cannot be normalized
     * @throws TypeDefinitionException in case of a strategy rejects the
     *         class or a property
     */
    public function normalize(mixed $value, Context $context): array|\stdClass
    {
        if (!$value instanceof $this->metadata->name) {
            throw InvalidValueException::becauseInvalidType($this->metadata->name, $value)
                ->withTemplate($this->metadata->typeErrorMessage);
        }

        $accessor = $context->accessors->get($this->metadata);
        $object = $value;

        try {
            foreach ($accessor->beforeNormalization as $hook) {
                $value = $hook->beforeNormalize($this->metadata, $value, $context);
            }
        } catch (NotInterceptableExceptionInterface|InvalidValueException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw InvalidValueException::becauseValueIsRejected($e, $object);
        }

        $result = [];

        foreach ($this->readable as $property) {
            $key = $property->metadata->externalName;

            try {
                $field = $accessor->readers[$property->metadata->name]->read($value);
            } catch (\Error $e) {
                throw InvalidValueException::wrapWithPath($e, new PropertySegment($key), null);
            }

            if ($property->metadata->skip !== [] && self::isSkipped($property, $value, $field)) {
                continue;
            }

            try {
                $result[$key] = $property->type->normalize($field, $context);
            } catch (NotInterceptableExceptionInterface $e) {
                throw $e;
            } catch (\Exception $e) {
                throw InvalidValueException::wrapWithPath(
                    $e,
                    new PropertySegment($key),
                    $field,
                    $property->metadata->typeErrorMessage,
                );
            }
        }

        try {
            foreach ($accessor->afterNormalization as $hook) {
                $result = $hook->afterNormalize($this->metadata, $value, $result, $context);
            }
        } catch (NotInterceptableExceptionInterface|InvalidValueException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw InvalidValueException::becauseValueIsRejected($e, $object);
        }

        if ($this->metadata->isNormalizeAsArray ?? $context->config->isObjectAsArray) {
            return $result;
        }

        return (object) $result;
    }

    /**
     * Returns the names of the property present in the data, in the order
     * of priority: the external name, then the aliases. A property without
     * aliases has none, its external name is taken as is.
     *
     * @param array<array-key, mixed> $value
     * @return list<non-empty-string>
     */
    private static function getPresentNames(PropertyMetadata $metadata, array $value): array
    {
        if ($metadata->aliases === []) {
            return [];
        }

        $present = [];

        foreach ([$metadata->externalName, ...$metadata->aliases] as $name) {
            if (\array_key_exists($name, $value)) {
                $present[] = $name;
            }
        }

        return $present;
    }

    /**
     * Returns the name the value of the property is taken from by its alias
     * conflict, {@see null} in case of the present names conflict.
     *
     * @param non-empty-list<non-empty-string> $present names of the property
     *        present in the data
     * @param array<array-key, mixed> $value
     * @return non-empty-string|null
     */
    private static function selectName(PropertyMetadata $metadata, array $present, array $value): ?string
    {
        if (\count($present) === 1) {
            return $present[0];
        }

        $conflict = $metadata->aliasConflict;
        $isNameFirst = $conflict === AliasConflict::UseNameOrFirst || $conflict === AliasConflict::UseNameOrLast;

        if ($isNameFirst && \in_array($metadata->externalName, $present, true)) {
            return $metadata->externalName;
        }

        return match ($conflict) {
            AliasConflict::Fail => null,
            AliasConflict::UseFirst, AliasConflict::UseNameOrFirst => self::sortByData($present, $value)[0],
            AliasConflict::UseLast, AliasConflict::UseNameOrLast => \array_reverse(self::sortByData($present, $value))[0],
        };
    }

    /**
     * Returns the names in the order of their keys in the data.
     *
     * @param non-empty-list<non-empty-string> $names
     * @param array<array-key, mixed> $value
     * @return non-empty-list<non-empty-string>
     */
    private static function sortByData(array $names, array $value): array
    {
        $positions = [];

        foreach (\array_keys($value) as $position => $key) {
            $positions[(string) $key] = $position;
        }

        \usort($names, static fn(string $a, string $b): int => $positions[$a] <=> $positions[$b]);

        return $names;
    }

    /**
     * Returns {@see true} in case of a skip condition of the property is
     * matched by the read value.
     *
     * @param Property<mixed> $property
     */
    private static function isSkipped(Property $property, object $object, mixed $field): bool
    {
        foreach ($property->metadata->skip as $condition) {
            if ($condition->isMatched($object, $field)) {
                return true;
            }
        }

        return false;
    }
}
