<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;

/**
 * @template-implements TypeInterface<int>
 */
class IntType implements TypeInterface
{
    public function match(mixed $value, Context $context): bool
    {
        return \is_int($value);
    }

    public function cast(mixed $value, Context $context): int
    {
        return match (true) {
            \is_int($value) => $value,
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
