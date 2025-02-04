<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Type;

use TypeLang\Mapper\Platform\Type\UnitEnumType\UnitEnumTypeDenormalizer;
use TypeLang\Mapper\Platform\Type\UnitEnumType\UnitEnumTypeNormalizer;

/**
 * @template-extends AsymmetricType<UnitEnumTypeNormalizer, UnitEnumTypeDenormalizer>
 */
class UnitEnumType extends AsymmetricType
{
    /**
     * @param class-string<\UnitEnum> $class
     * @param non-empty-list<non-empty-string> $cases
     */
    public function __construct(string $class, array $cases, TypeInterface $type)
    {
        parent::__construct(
            normalizer: new UnitEnumTypeNormalizer($class),
            denormalizer: new UnitEnumTypeDenormalizer($class, $cases, $type),
        );
    }
}
