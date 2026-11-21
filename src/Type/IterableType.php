<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\InvalidValueException;
use TypeLang\Mapper\Exception\NotInterceptableExceptionInterface;

/**
 * `iterable<K, V>`: an array or a {@see \Traversable}, read into an array
 * with its keys kept and converted by the array type in both directions.
 * A generator is read once: a check by `isNormalizable()` consumes it.
 *
 * @template T of array<array-key, mixed>
 *
 * @template-implements TypeInterface<T>
 */
final class IterableType implements TypeInterface
{
    public function __construct(
        /**
         * @var TypeInterface<T>
         */
        public readonly TypeInterface $array,
    ) {}

    public function isDenormalizable(mixed $value, Context $context): bool
    {
        try {
            $value = self::read($value);
        } catch (InvalidValueException) {
            return false;
        }

        return $this->array->isDenormalizable($value, $context);
    }

    /**
     * @throws InvalidValueException in case of the traversal fails or the
     *         value is not an iterable of the type
     */
    public function denormalize(mixed $value, Context $context): mixed
    {
        return $this->array->denormalize(self::read($value), $context);
    }

    public function isNormalizable(mixed $value, Context $context): bool
    {
        try {
            $value = self::read($value);
        } catch (InvalidValueException) {
            return false;
        }

        return $this->array->isNormalizable($value, $context);
    }

    /**
     * @throws InvalidValueException in case of the traversal fails or the
     *         value is not an iterable of the type
     */
    public function normalize(mixed $value, Context $context): mixed
    {
        return $this->array->normalize(self::read($value), $context);
    }

    /**
     * Returns the elements of a {@see \Traversable}, any other value as is.
     *
     * @throws InvalidValueException in case of the traversal fails or
     *         yields a key that is not an array key
     */
    private static function read(mixed $value): mixed
    {
        if (!$value instanceof \Traversable) {
            return $value;
        }

        $result = [];

        try {
            foreach ($value as $key => $element) {
                if (!\is_int($key) && !\is_string($key)) {
                    throw new \UnexpectedValueException(\sprintf(
                        'Key of type "%s" is not an array key',
                        \get_debug_type($key),
                    ));
                }

                $result[$key] = $element;
            }
        } catch (NotInterceptableExceptionInterface $e) {
            throw $e;
        } catch (\Exception $e) {
            throw InvalidValueException::becauseValueIsRejected($e, $value);
        }

        return $result;
    }
}
