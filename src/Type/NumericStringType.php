<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;

class NumericStringType implements TypeInterface
{
    public function match(mixed $value, Context $context): bool
    {
        return \is_string($value) && \is_numeric($value);
    }

    /**
     * @throws InvalidValueException
     */
    public function cast(mixed $value, Context $context): string
    {
        if ($this->match($value, $context)) {
            /** @var numeric-string */
            return $value;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
