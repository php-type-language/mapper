<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Runtime\Context;

class NullableType implements TypeInterface
{
    public function __construct(
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
