<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Type\ArrayType\ArrayTypeDenormalizer;
use TypeLang\Mapper\Type\ArrayType\ArrayTypeNormalizer;

/**
 * @template-extends AsymmetricType<ArrayTypeNormalizer, ArrayTypeDenormalizer>
 */
class ArrayType extends AsymmetricType
{
    public function __construct(
        TypeInterface $key = new ArrayKeyType(),
        TypeInterface $value = new MixedType(),
    ) {
        parent::__construct(
            normalizer: new ArrayTypeNormalizer($key, $value),
            denormalizer: new ArrayTypeDenormalizer($key, $value),
        );
    }
}
