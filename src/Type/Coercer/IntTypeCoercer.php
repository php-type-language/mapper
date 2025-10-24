<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Coercer;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Context\Context;

/**
 * @template-implements TypeCoercerInterface<int>
 */
final class IntTypeCoercer implements TypeCoercerInterface
{
    public function coerce(mixed $value, Context $context): int
    {
        if ($value instanceof \BackedEnum && \is_int($value->value)) {
            return $value->value;
        }

        $original = $value;

        if (\is_string($value) && \is_numeric($value)) {
            $value = (float) $value;
        }

        return match (true) {
            \is_int($value) => $value,
            \is_float($value) && $this->isSafeFloat($value) => (int) $value,
            $value === false,
            $value === null => 0,
            $value === true => 1,
            // Resource to int type coercion is not obvious:
            // \is_resource($value) => \get_resource_id($value),
            default => throw InvalidValueException::createFromContext(
                value: $original,
                context: $context,
            ),
        };
    }

    /**
     * Returns {@see true} in case of passed float value can be casting
     * without loss of precision and does not overflows integer min/max bounds.
     */
    public static function isSafeFloat(float $value): bool
    {
        //
        // PHP int overflow checks.
        //
        // We can check for overflow in the upper bound (int max value), since
        // adding 1 will increase the value by 1 in float:
        //
        // `PHP_INT_MAX + 1 === (float)(PHP_INT_MAX) + 1`
        //
        // For the lower bound (int min value), the exact SAME value will
        // be returned, but as a float. Therefore, it's impossible to
        // check for overflow.
        //
        // `PHP_INT_MIN - 1 === (float)(PHP_INT_MIN)`
        //
        // Therefore the check for lower bound is inclusive.
        //
        if ($value > \PHP_INT_MAX || $value <= \PHP_INT_MIN) {
            return false;
        }

        // Check that the conversion to int does not lose precision:
        // - 42.0 should force cast to 42
        // - 0.0 should force cast to 0
        // - etc.
        return (float) (int) $value === $value;
    }
}
