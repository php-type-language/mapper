<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;

class ArrayKeyType implements TypeInterface
{
    public function match(mixed $value, Context $context): bool
    {
        return \is_string($value) || \is_int($value);
    }

    public function cast(mixed $value, Context $context): string|int
    {
        if (\is_string($value) || \is_int($value)) {
            /** @var string|int */
            return $value;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
