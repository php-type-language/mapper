<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Coercer;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;

/**
 * @template-implements TypeCoercerInterface<array-key>
 */
final class ArrayKeyCoercer implements TypeCoercerInterface
{
    public function coerce(mixed $value, Context $context): int|string
    {
        if (\is_string($value)) {
            // String contains something like "42" or "42.0"
            $isIntNumeric = \is_numeric($value)
                && (float) $value === (float) (int) $value;

            return $isIntNumeric ? (int) $value : $value;
        }

        return match (true) {
            \is_int($value) => $value,
            $value === false,
            $value === null => 0,
            $value === true => 1,
            // Stringable
            \is_float($value),
            $value instanceof \Stringable => (string) $value,
            // Enum
            $value instanceof \BackedEnum => (string) $value->value,
            $value instanceof \UnitEnum => $value->name,
            default => throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            ),
        };
    }
}
