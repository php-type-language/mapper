<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Diagnostics\Path\Segment\KeySegment;
use TypeLang\Mapper\Exception\InvalidValueException;
use TypeLang\Mapper\Exception\NotInterceptableExceptionInterface;

/**
 * `array<K, V>`: an array with keys of the key type and elements of the
 * value type, the keys are kept. A key is checked by the key type in the
 * non-strict context, such as the key `1` of `{"1": ...}` for `string`.
 *
 * @template TKey of array-key
 * @template TValue
 *
 * @template-implements TypeInterface<array<TKey, TValue>>
 */
final class ArrayType implements TypeInterface
{
    public function __construct(
        /**
         * @var TypeInterface<TKey>
         */
        public readonly TypeInterface $key,
        /**
         * @var TypeInterface<TValue>
         */
        public readonly TypeInterface $value,
    ) {}

    public function isDenormalizable(mixed $value, Context $context): bool
    {
        $value = $context->coercers->array->coerce($value);

        if ($value === null) {
            return false;
        }

        $keys = $context->withStrict(false);

        foreach ($value as $key => $item) {
            if (!$this->key->isDenormalizable($key, $keys) || !$this->value->isDenormalizable($item, $context)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<TKey, TValue>
     * @throws InvalidValueException in case of the value is not an array or
     *         a key or an element cannot be denormalized
     */
    public function denormalize(mixed $value, Context $context): array
    {
        $input = $context->coercers->array->coerce($value)
            ?? throw InvalidValueException::becauseInvalidType($value);

        $limit = $context->config->maxViolations;
        $keys = $context->withStrict(false);
        $isAnyKey = $this->key instanceof ArrayKeyType;
        $result = [];
        $violations = [];

        foreach ($input as $key => $item) {
            /** @var TKey */
            $index = $key;

            try {
                if (!$isAnyKey) {
                    $index = $this->key->denormalize($key, $keys);
                }
            } catch (NotInterceptableExceptionInterface $e) {
                throw $e;
            } catch (\Exception) {
                InvalidValueException::collect(
                    $violations,
                    InvalidValueException::becauseInvalidKey($key),
                    $limit,
                );

                continue;
            }

            try {
                $result[$index] = $this->value->denormalize($item, $context);
            } catch (NotInterceptableExceptionInterface $e) {
                throw $e;
            } catch (\Exception $e) {
                InvalidValueException::collect(
                    $violations,
                    InvalidValueException::wrapWithPath($e, new KeySegment($key), $item),
                    $limit,
                );
            }
        }

        if ($violations !== []) {
            throw InvalidValueException::becauseViolationsAreCollected($violations);
        }

        return $result;
    }

    public function isNormalizable(mixed $value, Context $context): bool
    {
        if (!\is_array($value)) {
            return false;
        }

        foreach ($value as $key => $item) {
            if (!$this->key->isNormalizable($key, $context) || !$this->value->isNormalizable($item, $context)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<array-key, mixed>
     * @throws InvalidValueException in case of the value is not an array or
     *         a key or an element cannot be normalized
     */
    public function normalize(mixed $value, Context $context): array
    {
        if (!\is_array($value)) {
            throw InvalidValueException::becauseInvalidType($value);
        }

        $result = [];

        foreach ($value as $key => $item) {
            if (!$this->key->isNormalizable($key, $context)) {
                throw InvalidValueException::becauseInvalidKey($key);
            }

            try {
                $result[$key] = $this->value->normalize($item, $context);
            } catch (NotInterceptableExceptionInterface $e) {
                throw $e;
            } catch (\Exception $e) {
                throw InvalidValueException::wrapWithPath($e, new KeySegment($key), $item);
            }
        }

        return $result;
    }
}
