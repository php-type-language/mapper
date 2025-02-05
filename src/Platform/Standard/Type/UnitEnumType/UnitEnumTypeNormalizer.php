<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Standard\Type\UnitEnumType;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Platform\Standard\Type\TypeInterface;
use TypeLang\Mapper\Runtime\Context;

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
