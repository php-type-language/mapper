<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Standard\Type;

use TypeLang\Mapper\Platform\Standard\Type\BackedEnumType\BackedEnumTypeDenormalizer;
use TypeLang\Mapper\Platform\Standard\Type\BackedEnumType\BackedEnumTypeNormalizer;

/**
 * @template-extends AsymmetricType<BackedEnumTypeNormalizer, BackedEnumTypeDenormalizer>
 */
class BackedEnumType extends AsymmetricType
{
    /**
     * @param class-string<\BackedEnum> $class
     */
    public function __construct(string $class, TypeInterface $type)
    {
        parent::__construct(
            normalizer: new BackedEnumTypeNormalizer(
                class: $class,
            ),
            denormalizer: new BackedEnumTypeDenormalizer(
                class: $class,
                type: $type,
            ),
        );
    }
}
