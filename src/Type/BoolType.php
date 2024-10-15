<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context\LocalContext;

class BoolType implements TypeInterface
{
    public function match(mixed $value, LocalContext $context): bool
    {
        return \is_bool($value);
    }

    /**
     * Converts incoming value to the bool (in case of strict types is disabled).
     *
     * @throws InvalidValueException
     */
    public function cast(mixed $value, LocalContext $context): bool
    {
        if ($this->match($value, $context)) {
            /** @var bool */
            return $value;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
