<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\AttributeReader;

use ReflectionProperty as RefProperty;
use ReflectionMethod as RefHook;

abstract class PropertyAttributeLoader implements PropertyAttributeLoaderInterface
{
    /**
     * @template TAttribute of object
     *
     * @param class-string<TAttribute> $name
     *
     * @return TAttribute|null
     */
    protected function findPropertyAttribute(RefProperty|RefHook $property, string $name): ?object
    {
        foreach ($this->getAllPropertyAttributes($property, $name) as $attribute) {
            return $attribute;
        }

        return null;
    }

    /**
     * @template TAttribute of object
     *
     * @param class-string<TAttribute> $name
     *
     * @return iterable<array-key, TAttribute>
     */
    protected function getAllPropertyAttributes(RefProperty|RefHook $property, string $name): iterable
    {
        $attributes = $property->getAttributes($name, \ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            yield $attribute->newInstance();
        }
    }
}
