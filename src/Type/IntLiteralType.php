<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\ContextInterface;

class IntLiteralType extends IntType
{
    public function __construct(
        private readonly int $value,
    ) {
        parent::__construct($this->value, $this->value);
    }

    #[\Override]
    public function match(mixed $value, ContextInterface $context): bool
    {
        return $value === $this->value;
    }

    /**
     * @throws InvalidValueException
     */
    #[\Override]
    public function cast(mixed $value, ContextInterface $context): int
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
