<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\UnitEnumType;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Type\TypeInterface;

class UnitEnumTypeNormalizer implements TypeInterface
{
    /**
     * @param class-string<\UnitEnum> $class
     */
    public function __construct(
        protected readonly string $class,
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        return $value instanceof $this->class;
    }

    /**
     * Converts enum case (of {@see \UnitEnum}) objects to their
     * scalar representation.
     *
     * @throws InvalidValueException
     */
    public function cast(mixed $value, Context $context): string
    {
        if ($value instanceof $this->class) {
            return $value->name;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
