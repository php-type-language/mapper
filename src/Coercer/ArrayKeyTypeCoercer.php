<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Coercer;

use TypeLang\Mapper\Context\RuntimeContext;

/**
 * @template-implements TypeCoercerInterface<array-key>
 */
class ArrayKeyTypeCoercer implements TypeCoercerInterface
{
    public function tryCoerce(mixed $value, RuntimeContext $context): mixed
    {
        return match (true) {
            \is_string($value),
            \is_int($value) => $value,
            $value === false,
            $value === null => 0,
            $value === true => 1,
            // Stringable
            \is_float($value) && IntTypeCoercer::isSafeFloat($value) => (int) $value,
            $value instanceof \Stringable => (string) $value,
            // Enum
            $value instanceof \BackedEnum => $value->value,
            $value instanceof \UnitEnum => $value->name,
            default => $value,
        };
    }
}
