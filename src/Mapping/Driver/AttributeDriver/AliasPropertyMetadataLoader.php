<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver\AttributeDriver;

use TypeLang\Mapper\Mapping\MapName;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

final class AliasPropertyMetadataLoader extends PropertyMetadataLoader
{
    /**
     * @throws \Throwable
     */
    public function load(
        \ReflectionProperty $property,
        PropertyMetadata $metadata,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): void {
        $attribute = $this->findPropertyAttribute($property, MapName::class);

        if ($attribute === null) {
            return;
        }

        $metadata->alias = $attribute->name;
    }
}
