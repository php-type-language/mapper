<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ObjectType;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Type\TypeInterface;

final class ObjectTypeDenormalizer implements TypeInterface
{
    public function match(mixed $value, Context $context): bool
    {
        return \is_object($value) || \is_array($value);
    }

    /**
     * @throws InvalidValueException
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
