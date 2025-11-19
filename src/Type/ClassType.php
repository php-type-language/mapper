<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Kernel\Instantiator\ClassInstantiatorInterface;
use TypeLang\Mapper\Kernel\PropertyAccessor\PropertyAccessorInterface;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Type\ClassType\ClassFromArrayType;
use TypeLang\Mapper\Type\ClassType\ClassToArrayType;

/**
 * @template TObject of object = object
 * @template TResult of object|array = object|array<array-key, mixed>
 * @template-extends AsymmetricType<TResult, TObject>
 */
class ClassType extends AsymmetricType
{
    /**
     * @param ClassMetadata<TObject> $metadata
     */
    public function __construct(
        ClassMetadata $metadata,
        PropertyAccessorInterface $accessor,
        ClassInstantiatorInterface $instantiator,
    ) {
        parent::__construct(
            normalize: new ClassToArrayType(
                metadata: $metadata,
                accessor: $accessor,
            ),
            denormalize: new ClassFromArrayType(
                metadata: $metadata,
                accessor: $accessor,
                instantiator: $instantiator,
            ),
        );
    }
}
