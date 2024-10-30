<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Type\ClassType\ClassInstantiator\ClassInstantiatorInterface;
use TypeLang\Mapper\Type\ClassType\ClassTypeDenormalizer;
use TypeLang\Mapper\Type\ClassType\ClassTypeNormalizer;
use TypeLang\Mapper\Type\ClassType\PropertyAccessor\PropertyAccessorInterface;

/**
 * @template T of object
 * @template-extends AsymmetricType<ClassTypeNormalizer<T>, ClassTypeDenormalizer<T>>
 */
class ClassType extends AsymmetricType
{
    /**
     * @param ClassMetadata<T> $metadata
     */
    public function __construct(
        ClassMetadata $metadata,
        PropertyAccessorInterface $accessor,
        ClassInstantiatorInterface $instantiator,
    ) {
        parent::__construct(
            normalizer: new ClassTypeNormalizer(
                metadata: $metadata,
                accessor: $accessor,
            ),
            denormalizer: new ClassTypeDenormalizer(
                metadata: $metadata,
                accessor: $accessor,
                instantiator: $instantiator,
            ),
        );
    }
}
