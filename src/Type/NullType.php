<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template-implements TypeInterface<null>
 */
class NullType implements TypeInterface
{
    /**
     * @phpstan-assert-if-true null $value
     */
    public function match(mixed $value, RuntimeContext $context): bool
    {
        return $value === null;
    }

    public function cast(mixed $value, RuntimeContext $context): mixed
    {
        if ($value === null) {
            return null;
        }

        throw InvalidValueException::createFromContext($context);
    }
}
