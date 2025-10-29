<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template-implements TypeInterface<bool>
 */
class BoolLiteralType implements TypeInterface
{
    public function __construct(
        protected readonly bool $value,
    ) {}

    /**
     * @phpstan-assert-if-true bool $value
     */
    public function match(mixed $value, Context $context): bool
    {
        return $value === $this->value;
    }

    public function cast(mixed $value, Context $context): bool
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
