<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Type\ObjectType;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Platform\Type\TypeInterface;
use TypeLang\Mapper\Runtime\Context;

final class ObjectTypeNormalizer implements TypeInterface
{
    public function match(mixed $value, Context $context): bool
    {
        return \is_object($value);
    }

    /**
     * @return array<array-key, mixed>|object
     * @throws InvalidValueException in case the value is incorrect
     */
    public function cast(mixed $value, Context $context): array|object
    {
        if (!\is_object($value)) {
            throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            );
        }

        $result = \get_object_vars($value);

        if ($context->isObjectsAsArrays()) {
            return $result;
        }

        return (object) $result;
    }
}
