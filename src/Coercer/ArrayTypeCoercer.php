<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Coercer;

use TypeLang\Mapper\Context\RuntimeContext;

/**
 * @template-implements TypeCoercerInterface<array<array-key, mixed>>
 */
class ArrayTypeCoercer implements TypeCoercerInterface
{
    public function tryCoerce(mixed $value, RuntimeContext $context): mixed
    {
        return match (true) {
            \is_array($value) => $value,
            $value instanceof \Traversable => \iterator_to_array($value, true),
            \is_object($value) => (array) $value,
            default => $value,
        };
    }
}
