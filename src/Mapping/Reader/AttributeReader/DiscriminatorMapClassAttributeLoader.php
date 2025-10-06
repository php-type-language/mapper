<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\AttributeReader;

use TypeLang\Mapper\Mapping\DiscriminatorMap;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\DiscriminatorPrototype;
use TypeLang\Mapper\Mapping\Metadata\ClassPrototype;
use TypeLang\Mapper\Mapping\Metadata\TypePrototype;

final class DiscriminatorMapClassAttributeLoader extends ClassAttributeLoader
{
    public function load(\ReflectionClass $class, ClassPrototype $prototype): void
    {
        $attribute = $this->findClassAttribute($class, DiscriminatorMap::class);

        if ($attribute === null) {
            return;
        }

        $default = null;

        if ($attribute->otherwise !== null) {
            $default = new TypePrototype($attribute->otherwise);
        }

        $prototype->discriminator = new DiscriminatorPrototype(
            field: $attribute->field,
            default: $default,
        );

        foreach ($attribute->map as $value => $type) {
            $prototype->discriminator->map->add($value, new TypePrototype($type));
        }
    }
}
