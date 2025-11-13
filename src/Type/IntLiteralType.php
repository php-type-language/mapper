<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\RuntimeContext;
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
    public function match(mixed $value, RuntimeContext $context): bool
    {
        return $value === $this->value;
    }

    public function cast(mixed $value, RuntimeContext $context): int
    {
        if ($value === $this->value) {
            return $value;
        }

        throw InvalidValueException::createFromContext($context);
    }
}
