<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template-implements TypeInterface<float>
 */
class FloatType implements TypeInterface
{
    public function match(mixed $value, Context $context): bool
    {
        return \is_float($value);
    }

    public function cast(mixed $value, Context $context): float
    {
        return match (true) {
            \is_float($value) => $value,
            default => throw InvalidValueException::createFromContext($context),
        };
    }
}
