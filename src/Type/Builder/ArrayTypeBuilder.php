<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\ArrayType;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template TKey of array-key = array-key
 * @template TValue of mixed = mixed
 *
 * @template-extends MapTypeBuilder<TKey, TValue>
 */
class ArrayTypeBuilder extends MapTypeBuilder
{
    protected function create(TypeInterface $key, TypeInterface $value): ArrayType
    {
        return new ArrayType($key, $value);
    }
}
