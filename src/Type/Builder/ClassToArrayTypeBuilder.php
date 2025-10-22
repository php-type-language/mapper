<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Type\ClassType\ClassTypeNormalizer;
use TypeLang\Mapper\Type\TypeInterface;

class ClassToArrayTypeBuilder extends ClassTypeBuilder
{
    protected function create(ClassMetadata $metadata): TypeInterface
    {
        return new ClassTypeNormalizer(
            metadata: $metadata,
            accessor: $this->accessor,
        );
    }
}
