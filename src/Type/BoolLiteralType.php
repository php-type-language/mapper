<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\ContextInterface;

class BoolLiteralType extends BoolType
{
    public function __construct(
        private readonly bool $value,
    ) {}

    #[\Override]
    public function match(mixed $value, ContextInterface $context): bool
    {
        return $value === $this->value;
    }

    /**
     * Converts incoming value to the bool (in case of strict types is disabled).
     *
     * @throws InvalidValueException
     */
    #[\Override]
    public function cast(mixed $value, ContextInterface $context): bool
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
