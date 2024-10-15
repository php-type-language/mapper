<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context\LocalContext;

class StringType implements TypeInterface
{
    public function match(mixed $value, LocalContext $context): bool
    {
        return \is_string($value);
    }

    /**
     * Converts incoming value to the string (in case of strict types is disabled).
     *
     * @throws InvalidValueException
     */
    public function cast(mixed $value, LocalContext $context): string
    {
        if ($this->match($value, $context)) {
            /** @var string */
            return $value;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
