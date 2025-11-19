<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Type\BackedEnumType\BackedEnumFromScalarType;
use TypeLang\Mapper\Type\BackedEnumType\BackedEnumToScalarType;

/**
 * @template TEnum of \BackedEnum = \BackedEnum
 * @template-extends AsymmetricType<int|string, TEnum>
 */
class BackedEnumType extends AsymmetricType
{
    /**
     * @param class-string<TEnum> $class
     * @param TypeInterface<value-of<TEnum>> $type
     */
    public function __construct(string $class, TypeInterface $type)
    {
        parent::__construct(
            normalize: new BackedEnumToScalarType(
                class: $class
            ),
            denormalize: new BackedEnumFromScalarType(
                class: $class,
                type: $type
            ),
        );
    }
}
