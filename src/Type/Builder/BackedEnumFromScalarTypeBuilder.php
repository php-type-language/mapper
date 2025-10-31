<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\BackedEnumFromScalarType;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template TEnum of \BackedEnum = \BackedEnum
 * @template-extends BackedEnumTypeBuilder<TEnum, TEnum>
 */
class BackedEnumFromScalarTypeBuilder extends BackedEnumTypeBuilder
{
    protected function create(string $class, string $definition, TypeInterface $type): BackedEnumFromScalarType
    {
        return new BackedEnumFromScalarType($class, $type);
    }
}
