<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Runtime\Context;

/**
 * @template T of mixed = mixed
 * @template-implements TypeInterface<T|null>
 */
class NullableType implements TypeInterface
{
    public function __construct(
        /**
         * @var TypeInterface<T>
         */
        private readonly TypeInterface $parent,
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        return $value === null
            || $this->parent->match($value, $context);
    }

    public function cast(mixed $value, Context $context): mixed
    {
        if ($value === null) {
            return null;
        }

        return $this->parent->cast($value, $context);
    }
}
