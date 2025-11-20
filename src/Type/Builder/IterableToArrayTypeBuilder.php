<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\IterableToArrayType;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template TKey of array-key = array-key
 * @template TValue of mixed = mixed
 *
 * @template-extends MapTypeBuilder<TKey, TValue>
 */
class IterableToArrayTypeBuilder extends MapTypeBuilder
{
    protected function create(TypeInterface $key, TypeInterface $value): IterableToArrayType
    {
        return new IterableToArrayType($key, $value);
    }
}
