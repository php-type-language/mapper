<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;

class NullType implements TypeInterface
{
    public function match(mixed $value, Context $context): bool
    {
        return $value === null;
    }

    /**
     * @throws InvalidValueException
     */
    public function cast(mixed $value, Context $context): mixed
    {
        if ($this->match($value, $context)) {
            return null;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
