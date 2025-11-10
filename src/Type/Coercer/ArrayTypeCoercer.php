<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Coercer;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template-implements TypeCoercerInterface<array<array-key, mixed>>
 */
class ArrayTypeCoercer implements TypeCoercerInterface
{
    public function coerce(mixed $value, Context $context): array
    {
        return match (true) {
            \is_array($value) => $value,
            $value instanceof \Traversable => \iterator_to_array($value, true),
            default => throw InvalidValueException::createFromContext($context),
        };
    }
}
