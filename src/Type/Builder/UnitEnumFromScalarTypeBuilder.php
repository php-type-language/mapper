<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Mapper\Type\UnitEnumFromStringType;

/**
 * @template TEnum of \UnitEnum = \UnitEnum
 * @template-extends UnitEnumTypeBuilder<TEnum, TEnum>
 */
class UnitEnumFromScalarTypeBuilder extends UnitEnumTypeBuilder
{
    protected function create(string $class, array $cases, TypeInterface $type): UnitEnumFromStringType
    {
        return new UnitEnumFromStringType($class, $cases, $type);
    }
}
