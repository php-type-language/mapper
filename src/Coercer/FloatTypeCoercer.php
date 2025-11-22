<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Coercer;

use TypeLang\Mapper\Context\RuntimeContext;

/**
 * @template-implements TypeCoercerInterface<float>
 */
class FloatTypeCoercer implements TypeCoercerInterface
{
    public function tryCoerce(mixed $value, RuntimeContext $context): mixed
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
            default => $value,
        };
    }
}
