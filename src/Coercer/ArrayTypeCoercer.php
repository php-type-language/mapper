<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Coercer;

use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template-implements TypeCoercerInterface<array<array-key, mixed>>
 */
class ArrayTypeCoercer implements TypeCoercerInterface
{
    public function coerce(mixed $value, RuntimeContext $context): array
    {
        return match (true) {
            \is_array($value) => $value,
            $value instanceof \Traversable => \iterator_to_array($value, true),
            default => throw InvalidValueException::createFromContext($context),
        };
    }
}
