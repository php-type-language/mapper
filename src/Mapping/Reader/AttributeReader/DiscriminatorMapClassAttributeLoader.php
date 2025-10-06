<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\AttributeReader;

use TypeLang\Mapper\Mapping\DiscriminatorMap;
use TypeLang\Mapper\Mapping\Metadata\ClassInfo;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\DiscriminatorInfo;
use TypeLang\Mapper\Mapping\Metadata\TypeInfo;

final class DiscriminatorMapClassAttributeLoader extends ClassAttributeLoader
{
    public function load(ClassInfo $info, \ReflectionClass $class): void
    {
        $attribute = $this->findClassAttribute($class, DiscriminatorMap::class);

        if ($attribute === null) {
            return;
        }

        $default = null;

        if ($attribute->otherwise !== null) {
            $default = new TypeInfo($attribute->otherwise);
        }

        $map = [];

        foreach ($attribute->map as $value => $type) {
            $map[$value] = new TypeInfo($type);
        }

        $info->discriminator = new DiscriminatorInfo(
            field: $attribute->field,
            map: $map,
            default: $default,
        );
    }
}
