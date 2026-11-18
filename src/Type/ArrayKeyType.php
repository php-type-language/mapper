<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\InvalidValueException;

/**
 * `array-key`: an integer or a string, the keys a PHP array may have.
 *
 * @template-implements TypeInterface<int|string>
 */
final class ArrayKeyType implements TypeInterface
{
    public function isDenormalizable(mixed $value, Context $context): bool
    {
        return \is_int($value) || \is_string($value);
    }

    /**
     * @throws InvalidValueException in case of the value is neither an
     *         integer nor a string
     */
    public function denormalize(mixed $value, Context $context): int|string
    {
        if (\is_int($value) || \is_string($value)) {
            return $value;
        }

        throw InvalidValueException::becauseInvalidType($value);
    }

    public function isNormalizable(mixed $value, Context $context): bool
    {
        return \is_int($value) || \is_string($value);
    }

    /**
     * @throws InvalidValueException in case of the value is neither an
     *         integer nor a string
     */
    public function normalize(mixed $value, Context $context): int|string
    {
        return $this->denormalize($value, $context);
    }
}
