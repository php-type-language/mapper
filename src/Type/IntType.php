<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;

class IntType implements TypeInterface
{
    public function match(mixed $value, Context $context): bool
    {
        return \is_int($value);
    }

    /**
     * @throws InvalidValueException
     */
    public function cast(mixed $value, Context $context): int
    {
        if (\is_int($value)) {
            return $value;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
