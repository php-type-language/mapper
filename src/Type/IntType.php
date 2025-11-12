<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\MappingContext;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template-implements TypeInterface<int>
 */
class IntType implements TypeInterface
{
    /**
     * @phpstan-assert-if-true int $value
     */
    public function match(mixed $value, MappingContext $context): bool
    {
        return \is_int($value);
    }

    public function cast(mixed $value, MappingContext $context): int
    {
        return match (true) {
            \is_int($value) => $value,
            default => throw InvalidValueException::createFromContext($context),
        };
    }
}
