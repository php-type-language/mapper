<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\MappingContext;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template-implements TypeInterface<null>
 */
class NullType implements TypeInterface
{
    /**
     * @phpstan-assert-if-true null $value
     */
    public function match(mixed $value, MappingContext $context): bool
    {
        return $value === null;
    }

    public function cast(mixed $value, MappingContext $context): mixed
    {
        if ($value === null) {
            return null;
        }

        throw InvalidValueException::createFromContext($context);
    }
}
