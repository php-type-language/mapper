<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;

/**
 * @template-implements TypeInterface<float|int>
 */
class FloatType implements TypeInterface
{
    public function match(mixed $value, Context $context): bool
    {
        if ($context->isNormalization()) {
            return \is_float($value) || \is_int($value);
        }

        return \is_float($value);
    }

    public function cast(mixed $value, Context $context): float
    {
        if (\is_float($value) || \is_int($value)) {
            return (float) $value;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
