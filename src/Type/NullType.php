<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;

/**
 * @template-implements TypeInterface<null>
 */
class NullType implements TypeInterface
{
    public function match(mixed $value, Context $context): bool
    {
        return $value === null;
    }

    public function cast(mixed $value, Context $context): mixed
    {
        if ($value === null) {
            return null;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
