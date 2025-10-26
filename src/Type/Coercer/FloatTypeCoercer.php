<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Coercer;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template-implements TypeCoercerInterface<float>
 */
class FloatTypeCoercer implements TypeCoercerInterface
{
    public function coerce(mixed $value, Context $context): float
    {
        if ($value instanceof \BackedEnum && \is_int($value->value)) {
            return (float) $value->value;
        }

        return match (true) {
            \is_float($value),
            \is_integer($value),
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
