<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template-implements TypeInterface<bool>
 */
class BoolType implements TypeInterface
{
    /**
     * @phpstan-assert-if-true bool $value
     */
    public function match(mixed $value, Context $context): bool
    {
        return \is_bool($value);
    }

    public function cast(mixed $value, Context $context): bool
    {
        return match (true) {
            \is_bool($value) => $value,
            default => throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            ),
        };
    }
}
