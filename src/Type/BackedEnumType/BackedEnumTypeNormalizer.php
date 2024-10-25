<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\BackedEnumType;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Type\TypeInterface;

class BackedEnumTypeNormalizer implements TypeInterface
{
    /**
     * @param class-string<\BackedEnum> $class
     */
    public function __construct(
        protected readonly string $class,
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        return $value instanceof $this->class;
    }

    /**
     * Converts enum case (of {@see \BackedEnum}) objects to their
     * scalar representation.
     *
     * @throws InvalidValueException
     */
    public function cast(mixed $value, Context $context): int|string
    {
        if (!$value instanceof $this->class) {
            throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            );
        }

        return $value->value;
    }
}
