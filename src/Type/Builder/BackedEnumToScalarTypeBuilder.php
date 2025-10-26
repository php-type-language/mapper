<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\BackedEnumToScalarType;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template TEnum of \BackedEnum = \BackedEnum
 * @template-extends BackedEnumTypeBuilder<TEnum, value-of<TEnum>>
 */
class BackedEnumToScalarTypeBuilder extends BackedEnumTypeBuilder
{
    /** @phpstan-ignore-next-line : It's too difficult for PHPStan to calculate the specified type */
    protected function create(string $class, TypeInterface $type): BackedEnumToScalarType
    {
        return new BackedEnumToScalarType($class);
    }
}
