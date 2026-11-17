<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Tests\Stub;

use TypeLang\Mapper\Bridge\Symfony\Attribute\AsMapperAccessor;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\PropertyAccessor\PropertyReaderInterface;
use TypeLang\Mapper\PropertyAccessor\PropertyWriterInterface;
use TypeLang\Mapper\PropertyAccessor\Strategy\AccessStrategyInterface;

#[AsMapperAccessor]
final class AccessorStub implements AccessStrategyInterface
{
    public function tryCreateReader(\ReflectionProperty $property, PropertyMetadata $metadata): ?PropertyReaderInterface
    {
        return null;
    }

    public function tryCreateWriter(\ReflectionProperty $property, PropertyMetadata $metadata): ?PropertyWriterInterface
    {
        return null;
    }
}
