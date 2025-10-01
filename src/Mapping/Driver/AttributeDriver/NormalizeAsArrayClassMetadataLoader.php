<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver\AttributeDriver;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\NormalizeAsArray;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

final class NormalizeAsArrayClassMetadataLoader extends ClassMetadataLoader
{
    public function load(
        \ReflectionClass $class,
        ClassMetadata $metadata,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): void {
        $attribute = $this->findClassAttribute($class, NormalizeAsArray::class);

        if ($attribute === null) {
            return;
        }

        $metadata->isNormalizeAsArray = $attribute->enabled;
    }
}
