<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context\LocalContext;

class IntLiteralType extends IntType
{
    public function __construct(
        private readonly int $value,
    ) {
        parent::__construct($this->value, $this->value);
    }

    #[\Override]
    public function match(mixed $value, LocalContext $context): bool
    {
        return $value === $this->value;
    }

    #[\Override]
    public function cast(mixed $value, LocalContext $context): int
    {
        if ($this->match($value, $context)) {
            /** @var int */
            return $value;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
