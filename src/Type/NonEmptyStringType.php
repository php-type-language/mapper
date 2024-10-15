<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context\LocalContext;

class NonEmptyStringType implements TypeInterface
{
    public function match(mixed $value, LocalContext $context): bool
    {
        return $value !== '' && \is_string($value);
    }

    /**
     * @throws InvalidValueException
     */
    public function cast(mixed $value, LocalContext $context): string
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
