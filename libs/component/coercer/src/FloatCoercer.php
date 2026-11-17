<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Coercer;

/**
 * Converts numeric strings and exactly representable integers to `float`.
 *
 * @template-implements CoercerInterface<float>
 */
final class FloatCoercer implements CoercerInterface
{
    private const FLOAT_UPPER_BOUND = \PHP_INT_MAX + 1;

    /**
     * Length of the longest integer string whose number always has an exact
     * `float`: 15 digits are below `2 ** 53`
     */
    private const EXACT_INTEGER_STRING_LENGTH = 15;

    public function coerce(mixed $value): ?float
    {
        if (\is_float($value)) {
            return $value;
        }

        if (\is_string($value)) {
            return self::coerceString($value);
        }

        if (\is_int($value)) {
            return self::coerceInt($value);
        }

        return null;
    }

    private static function coerceString(string $value): ?float
    {
        if (!\is_numeric($value) || self::isPadded($value)) {
            return null;
        }

        $number = (float) $value;

        if (!\is_finite($number)) {
            return null;
        }

        if ($number === 0.0 && self::hasNonZeroMantissa($value)) {
            return null;
        }

        if (!self::isExactInteger($value, $number)) {
            return null;
        }

        return $number;
    }

    /**
     * Returns {@see false} for a string in integer notation whose number
     * has no exact `float`, such as `'9007199254740993'`. Strings with a
     * fraction or an exponent are rounded to the nearest `float`.
     *
     * @param numeric-string $value
     */
    private static function isExactInteger(string $value, float $number): bool
    {
        if (\strlen($value) <= self::EXACT_INTEGER_STRING_LENGTH || \strpbrk($value, '.eE') !== false) {
            return true;
        }

        $digits = \ltrim($value, '+-0');

        if ($digits === '') {
            return true;
        }

        return \sprintf('%.0F', \abs($number)) === $digits;
    }

    /**
     * @param numeric-string $value
     */
    private static function hasNonZeroMantissa(string $value): bool
    {
        return \strcspn($value, '123456789') < \strcspn($value, 'eE');
    }

    private static function coerceInt(int $value): ?float
    {
        $number = (float) $value;

        if ($number < self::FLOAT_UPPER_BOUND && (int) $number === $value) {
            return $number;
        }

        return null;
    }

    /**
     * Returns {@see true} if the numeric string has leading or trailing
     * whitespace.
     *
     * @param numeric-string $value
     */
    private static function isPadded(string $value): bool
    {
        return \ord($value[0]) <= 0x20 || \ord($value[-1]) <= 0x20;
    }
}
