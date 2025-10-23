<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Coercer;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;

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

        return match (true) {
            \is_int($value) => $value,
            $value === false,
            $value === null => 0,
            $value === true => 1,
            \is_resource($value) => \get_resource_id($value),
            // Check that the conversion to int does not lose precision.
            \is_numeric($value) && (float) (int) $value === (float) $value => (int) $value,
            default => throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            ),
        };
    }
}
