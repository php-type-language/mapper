<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Coercer;

use TypeLang\Mapper\Context\RuntimeContext;

/**
 * @template-implements TypeCoercerInterface<list<mixed>>
 */
class ListTypeCoercer implements TypeCoercerInterface
{
    public function tryCoerce(mixed $value, RuntimeContext $context): mixed
    {
        return match (true) {
            \is_array($value) => \array_is_list($value) ? $value : \array_values($value),
            $value instanceof \Traversable => \iterator_to_array($value, false),
            \is_object($value) => \array_values((array) $value),
            default => $value,
        };
    }
}
