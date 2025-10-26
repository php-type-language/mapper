<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Type\ClassTypeFromArrayType;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template TObject of object = object
 * @template-extends ClassTypeBuilder<TObject, TObject>
 */
class ClassFromArrayTypeBuilder extends ClassTypeBuilder
{
    protected function create(ClassMetadata $metadata): TypeInterface
    {
        return new ClassTypeFromArrayType(
            metadata: $metadata,
            accessor: $this->accessor,
            instantiator: $this->instantiator,
        );
    }
}
