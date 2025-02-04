<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Type;

use TypeLang\Mapper\Platform\Type\ObjectType\ObjectTypeDenormalizer;
use TypeLang\Mapper\Platform\Type\ObjectType\ObjectTypeNormalizer;

/**
 * @template-extends AsymmetricType<ObjectTypeNormalizer, ObjectTypeDenormalizer>
 */
class ObjectType extends AsymmetricType
{
    public function __construct()
    {
        parent::__construct(
            normalizer: new ObjectTypeNormalizer(),
            denormalizer: new ObjectTypeDenormalizer(),
        );
    }
}
