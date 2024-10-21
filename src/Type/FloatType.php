<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;

class FloatType implements TypeInterface
{
    public function match(mixed $value, Context $context): bool
    {
        return \is_float($value) || \is_int($value);
    }

    /**
     * @throws InvalidValueException
     */
    public function cast(mixed $value, Context $context): float
    {
        if ($this->match($value, $context)) {
            /** @var float|int $value */
            return (float) $value;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
