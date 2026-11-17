<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;

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
        return $this->array->isDenormalizable(self::read($value), $context);
    }

    public function denormalize(mixed $value, Context $context): mixed
    {
        return $this->array->denormalize(self::read($value), $context);
    }

    public function isNormalizable(mixed $value, Context $context): bool
    {
        return $this->array->isNormalizable(self::read($value), $context);
    }

    public function normalize(mixed $value, Context $context): mixed
    {
        return $this->array->normalize(self::read($value), $context);
    }

    /**
     * Returns the elements of a {@see \Traversable}, any other value as is.
     */
    private static function read(mixed $value): mixed
    {
        if ($value instanceof \Traversable) {
            return \iterator_to_array($value);
        }

        return $value;
    }
}
