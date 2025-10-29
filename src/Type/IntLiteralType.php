<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template-implements TypeInterface<int>
 */
class IntLiteralType implements TypeInterface
{
    public function __construct(
        protected readonly int $value,
    ) {}

    /**
     * @phpstan-assert-if-true int $value
     */
    public function match(mixed $value, Context $context): bool
    {
        return $value === $this->value;
    }

    public function cast(mixed $value, Context $context): int
    {
        if ($value === $this->value) {
            return $value;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
