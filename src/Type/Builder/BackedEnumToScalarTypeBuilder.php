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
    /** @phpstan-ignore-next-line : Too complicated return type checks for PHPStan */
    protected function create(string $class, string $definition, TypeInterface $type): BackedEnumToScalarType
    {
        return new BackedEnumToScalarType($class);
    }
}
