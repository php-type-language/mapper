<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Type\ObjectType\ObjectInstantiator\ObjectInstantiatorInterface;
use TypeLang\Mapper\Type\ObjectType\ObjectTypeDenormalizer;
use TypeLang\Mapper\Type\ObjectType\ObjectTypeNormalizer;
use TypeLang\Mapper\Type\ObjectType\PropertyAccessor\PropertyAccessorInterface;

/**
 * @template T of object
 * @template-extends AsymmetricType<ObjectTypeNormalizer<T>, ObjectTypeDenormalizer<T>>
 */
class ObjectType extends AsymmetricType
{
    /**
     * @param ClassMetadata<T> $metadata
     */
    public function __construct(
        ClassMetadata $metadata,
        PropertyAccessorInterface $accessor,
        ObjectInstantiatorInterface $instantiator,
    ) {
        parent::__construct(
            normalizer: new ObjectTypeNormalizer(
                metadata: $metadata,
                accessor: $accessor,
            ),
            denormalizer: new ObjectTypeDenormalizer(
                metadata: $metadata,
                accessor: $accessor,
                instantiator: $instantiator,
            ),
        );
    }
}
