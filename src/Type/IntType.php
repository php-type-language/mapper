<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;

class IntType implements TypeInterface
{
    public function match(mixed $value, Context $context): bool
    {
        return \is_int($value)
            // Assert for unary minus operation that overflows min int value.
            //
            // This is what happens:
            // 1) 9223372036854775808 is parsed by the PHP and converted
            //    to float(9.2233720368548E+18) on overflow.
            // 2) The unary minus ("-") operator is applied to the
            //    float(9.2233720368548E+18), yielding float(-9.2233720368548E+18)
            //    as the output.
            //
            // The set of specified operations compares negative floats
            // (after the operations performed) with the input float value.
            //
            // @phpstan-ignore-next-line : Support for PHP x86 implicit int conversion
            || $value === -2147483648
            // @phpstan-ignore-next-line : Support for PHP x64 implicit int conversion
            || $value === -9223372036854775808;
    }

    public function cast(mixed $value, Context $context): int
    {
        return match (true) {
            \is_int($value) => $value,
            // @phpstan-ignore-next-line : In case of PHP x86 the value will be casted to float
            $value === -2147483648,
            $value === -9223372036854775808 => \PHP_INT_MIN,
            !$context->isStrictTypesEnabled() => $this->convertToInt($value, $context),
            default => throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            ),
        };
    }

    /**
     * @throws InvalidValueException
     */
    protected function convertToInt(mixed $value, Context $context): int
    {
        if ($value instanceof \BackedEnum && \is_int($value->value)) {
            $value = $value->value;
        }

        return match (true) {
            \is_int($value) => $value,
            $value === false,
            $value === null => 0,
            $value === true => 1,
            // Check that the conversion to int does not lose precision.
            \is_numeric($value) && (float) (int) $value === (float) $value => (int) $value,
            default => throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            ),
        };
    }
}
