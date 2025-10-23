<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Coercer;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;

/**
 * @template-implements TypeCoercerInterface<float>
 */
final class FloatTypeCoercer implements TypeCoercerInterface
{
    public function coerce(mixed $value, Context $context): float
    {
        if ($value instanceof \BackedEnum && \is_int($value->value)) {
            return (float) $value->value;
        }

        return match (true) {
            \is_numeric($value) => (float) $value,
            $value === false,
            $value === null => 0.0,
            $value === true => 1.0,
            default => throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            ),
        };
    }
}
