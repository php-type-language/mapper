<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template-implements TypeInterface<string>
 */
class StringType implements TypeInterface
{
    public function match(mixed $value, Context $context): bool
    {
        return \is_string($value);
    }

    public function cast(mixed $value, Context $context): string
    {
        return match (true) {
            \is_string($value) => $value,
            default => throw InvalidValueException::createFromContext($context),
        };
    }
}
