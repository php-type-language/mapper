<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template-implements TypeInterface<array-key>
 */
class ArrayKeyType implements TypeInterface
{
    /**
     * @phpstan-assert-if-true array-key $value
     */
    public function match(mixed $value, RuntimeContext $context): bool
    {
        // TBD (?)
        // It is not entirely clear whether a zero ("0") string
        // key should be allowed, since it is technically
        // impossible to put it in associative array.
        //
        // if ($value === '0') {
        //     return false;
        // }

        return \is_int($value) || \is_string($value);
    }

    public function cast(mixed $value, RuntimeContext $context): string|int
    {
        return match (true) {
            \is_string($value),
            \is_int($value) => $value,
            default => throw InvalidValueException::createFromContext($context),
        };
    }
}
