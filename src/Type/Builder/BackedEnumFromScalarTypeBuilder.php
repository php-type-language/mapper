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
    protected function create(string $class, TypeInterface $type): BackedEnumFromScalarType
    {
        /** @phpstan-ignore-next-line : It's too difficult for PHPStan to calculate the specified type */
        return new BackedEnumFromScalarType($class, $type);
    }
}
