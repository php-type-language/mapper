<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\RuntimeExceptionInterface;
use TypeLang\Mapper\Runtime\Context\LocalContext;

class NullableType implements TypeInterface
{
    public function __construct(
        private readonly TypeInterface $parent,
    ) {}

    public function match(mixed $value, LocalContext $context): bool
    {
        return $value === null
            || $this->parent->match($value, $context);
    }

    /**
     * @throws RuntimeExceptionInterface
     * @throws \Throwable
     */
    public function cast(mixed $value, LocalContext $context): mixed
    {
        if ($value === null) {
            return null;
        }

        return $this->parent->cast($value, $context);
    }
}
