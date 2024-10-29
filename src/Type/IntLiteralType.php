<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;

class IntLiteralType implements TypeInterface
{
    public function __construct(
        private readonly int $value,
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        return $value === $this->value;
    }

    /**
     * @throws InvalidValueException
     */
    public function cast(mixed $value, Context $context): int
    {
        if ($value === $this->value) {
            /** @var int */
            return $value;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
