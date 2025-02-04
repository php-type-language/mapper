<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Type\ObjectType;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Platform\Type\TypeInterface;
use TypeLang\Mapper\Runtime\Context;

final class ObjectTypeDenormalizer implements TypeInterface
{
    public function match(mixed $value, Context $context): bool
    {
        return \is_object($value)
            || \is_array($value);
    }

    /**
     * @throws InvalidValueException in case the value is incorrect
     */
    public function cast(mixed $value, Context $context): object
    {
        if (\is_array($value)) {
            $value = (object) $value;
        }

        if (\is_object($value)) {
            return $value;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
