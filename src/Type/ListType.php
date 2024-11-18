<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Type\ListType\ListTypeDenormalizer;
use TypeLang\Mapper\Type\ListType\ListTypeNormalizer;

/**
 * @template-extends AsymmetricType<ListTypeNormalizer, ListTypeDenormalizer>
 */
class ListType extends AsymmetricType
{
    public function __construct(
        TypeInterface $value = new MixedType(),
    ) {
        parent::__construct(
            normalizer: new ListTypeNormalizer($value),
            denormalizer: new ListTypeDenormalizer($value),
        );
    }
}
