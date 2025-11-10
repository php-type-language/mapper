<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Coercer;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template-implements TypeCoercerInterface<list<mixed>>
 */
class ListTypeCoercer implements TypeCoercerInterface
{
    public function coerce(mixed $value, Context $context): array
    {
        return match (true) {
            \is_array($value) => \array_is_list($value) ? $value : \array_values($value),
            $value instanceof \Traversable => \iterator_to_array($value, false),
            default => throw InvalidValueException::createFromContext($context),
        };
    }
}
