<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template-implements TypeInterface<int>
 */
class IntType implements TypeInterface
{
    /**
     * @phpstan-assert-if-true int $value
     */
    public function match(mixed $value, Context $context): bool
    {
        return \is_int($value);
    }

    public function cast(mixed $value, Context $context): int
    {
        return match (true) {
            \is_int($value) => $value,
            default => throw InvalidValueException::createFromContext($context),
        };
    }
}
