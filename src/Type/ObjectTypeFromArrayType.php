<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template-implements TypeInterface<object>
 */
final class ObjectTypeFromArrayType implements TypeInterface
{
    public function match(mixed $value, Context $context): bool
    {
        return \is_object($value)
            || \is_array($value);
    }

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
