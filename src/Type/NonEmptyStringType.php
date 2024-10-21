<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\ContextInterface;

class NonEmptyStringType implements TypeInterface
{
    public function match(mixed $value, ContextInterface $context): bool
    {
        return $value !== '' && \is_string($value);
    }

    /**
     * @throws InvalidValueException
     */
    public function cast(mixed $value, ContextInterface $context): string
    {
        if ($this->match($value, $context)) {
            /** @var class-string */
            return $value;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
