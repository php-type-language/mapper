<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\UnitEnumType;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template TEnum of \UnitEnum = \UnitEnum
 *
 * @template-implements TypeInterface<non-empty-string>
 */
class UnitEnumToStringType implements TypeInterface
{
    public function __construct(
        /**
         * @var class-string<TEnum>
         */
        protected readonly string $class,
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        return $value instanceof $this->class;
    }

    public function cast(mixed $value, Context $context): string
    {
        if ($value instanceof $this->class) {
            /** @var non-empty-string */
            return $value->name;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
