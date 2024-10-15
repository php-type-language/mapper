<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context\LocalContext;

class NullType implements TypeInterface
{
    public function match(mixed $value, LocalContext $context): bool
    {
        return $value === null;
    }

    /**
     * @throws InvalidValueException
     */
    public function cast(mixed $value, LocalContext $context): mixed
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
