<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template-implements TypeInterface<object|array<array-key, mixed>>
 */
final class ObjectToArrayType implements TypeInterface
{
    public function match(mixed $value, RuntimeContext $context): bool
    {
        return \is_object($value);
    }

    public function cast(mixed $value, RuntimeContext $context): array|object
    {
        if (!\is_object($value)) {
            throw InvalidValueException::createFromContext($context);
        }

        $result = \get_object_vars($value);

        if ($context->isObjectAsArray()) {
            return $result;
        }

        return (object) $result;
    }
}
