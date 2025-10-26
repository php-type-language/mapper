<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Type\ClassTypeToArrayType;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template TObject of object = object
 * @template-extends ClassTypeBuilder<TObject, object|array<array-key, mixed>>
 */
class ClassToArrayTypeBuilder extends ClassTypeBuilder
{
    protected function create(ClassMetadata $metadata): TypeInterface
    {
        return new ClassTypeToArrayType(
            metadata: $metadata,
            accessor: $this->accessor,
        );
    }
}
