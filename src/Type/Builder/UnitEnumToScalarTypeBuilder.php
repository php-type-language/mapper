<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Mapper\Type\UnitEnumType\UnitEnumToStringType;

/**
 * @template TEnum of \UnitEnum = \UnitEnum
 *
 * @template-extends UnitEnumTypeBuilder<TEnum, non-empty-string>
 */
class UnitEnumToScalarTypeBuilder extends UnitEnumTypeBuilder
{
    protected function create(string $class, array $cases, TypeInterface $type): UnitEnumToStringType
    {
        return new UnitEnumToStringType($class);
    }
}
