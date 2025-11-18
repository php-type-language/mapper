<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ObjectType;

use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template-implements TypeInterface<object>
 */
final class ObjectFromArrayType implements TypeInterface
{
    public function match(mixed $value, RuntimeContext $context): bool
    {
        return \is_object($value)
            || \is_array($value);
    }

    public function cast(mixed $value, RuntimeContext $context): object
    {
        if (\is_array($value)) {
            $value = (object) $value;
        }

        if (\is_object($value)) {
            return $value;
        }

        throw InvalidValueException::createFromContext($context);
    }
}
