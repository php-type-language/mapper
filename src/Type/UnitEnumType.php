<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Type\UnitEnumType\UnitEnumFromStringType;
use TypeLang\Mapper\Type\UnitEnumType\UnitEnumToStringType;

/**
 * @template TEnum of \UnitEnum = \UnitEnum
 *
 * @template-extends AsymmetricType<non-empty-string, TEnum>
 */
class UnitEnumType extends AsymmetricType
{
    /**
     * @param class-string<TEnum> $class
     * @param TypeInterface<string> $type
     */
    public function __construct(string $class, TypeInterface $type = new StringType())
    {
        parent::__construct(
            normalize: new UnitEnumToStringType(
                class: $class,
            ),
            denormalize: new UnitEnumFromStringType(
                class: $class,
                type: $type,
            ),
        );
    }
}
