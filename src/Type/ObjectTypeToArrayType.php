<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;

/**
 * @template-implements TypeInterface<object|array<array-key, mixed>>
 */
final class ObjectTypeToArrayType implements TypeInterface
{
    public function match(mixed $value, Context $context): bool
    {
        return \is_object($value);
    }

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
