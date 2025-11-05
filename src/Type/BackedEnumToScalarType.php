<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template TEnum of \BackedEnum = \BackedEnum
 * @template-implements TypeInterface<value-of<TEnum>>
 */
class BackedEnumToScalarType implements TypeInterface
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

    public function cast(mixed $value, Context $context): int|string
    {
        if (!$value instanceof $this->class) {
            throw InvalidValueException::createFromContext($context);
        }

        return $value->value;
    }
}
