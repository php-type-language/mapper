<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Type\ClassType\ClassTypeDenormalizer;
use TypeLang\Mapper\Type\TypeInterface;

class ClassFromArrayTypeBuilder extends ClassTypeBuilder
{
    protected function create(ClassMetadata $metadata): TypeInterface
    {
        return new ClassTypeDenormalizer(
            metadata: $metadata,
            accessor: $this->accessor,
            instantiator: $this->instantiator,
        );
    }
}
