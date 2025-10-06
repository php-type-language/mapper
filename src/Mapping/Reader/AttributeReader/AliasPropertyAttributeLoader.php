<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\AttributeReader;

use TypeLang\Mapper\Mapping\MapName;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyInfo;

final class AliasPropertyAttributeLoader extends PropertyAttributeLoader
{
    public function load(\ReflectionProperty $property, PropertyInfo $info): void
    {
        $attribute = $this->findPropertyAttribute($property, MapName::class);

        if ($attribute === null) {
            return;
        }

        $info->alias = $attribute->name;
    }
}
