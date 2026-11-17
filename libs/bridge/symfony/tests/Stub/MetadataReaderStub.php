<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Tests\Stub;

use TypeLang\Mapper\Bridge\Symfony\Attribute\AsMapperMetadataReader;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Reader\MetadataReaderInterface;

#[AsMapperMetadataReader]
final class MetadataReaderStub implements MetadataReaderInterface
{
    public function read(\ReflectionClass $class, ?ClassMetadata $metadata = null): ClassMetadata
    {
        return $metadata ?? new ClassMetadata($class->getName());
    }
}
