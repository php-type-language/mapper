<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\MappingContext;

/**
 * @template-covariant TResult of mixed = mixed
 * @template-implements TypeInterface<TResult|null>
 */
class NullableType implements TypeInterface
{
    public function __construct(
        /**
         * @var TypeInterface<TResult>
         */
        private readonly TypeInterface $parent,
    ) {}

    public function match(mixed $value, MappingContext $context): bool
    {
        return $value === null || $this->parent->match($value, $context);
    }

    public function cast(mixed $value, MappingContext $context): mixed
    {
        if ($value === null) {
            return null;
        }

        return $this->parent->cast($value, $context);
    }
}
