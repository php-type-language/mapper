<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\AttributeReader;

use ReflectionMethod as RefHook;
use ReflectionProperty as RefProperty;

abstract class PropertyAttributeLoader implements PropertyAttributeLoaderInterface
{
    /**
     * @template TArgAttribute of object
     *
     * @param class-string<TArgAttribute> $name
     *
     * @return TArgAttribute|null
     */
    protected function findPropertyAttribute(RefProperty|RefHook $property, string $name): ?object
    {
        foreach ($this->getAllPropertyAttributes($property, $name) as $attribute) {
            return $attribute;
        }

        return null;
    }

    /**
     * @template TArgAttribute of object
     *
     * @param class-string<TArgAttribute> $name
     *
     * @return iterable<array-key, TArgAttribute>
     */
    protected function getAllPropertyAttributes(RefProperty|RefHook $property, string $name): iterable
    {
        $attributes = $property->getAttributes($name, \ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            yield $attribute->newInstance();
        }
    }
}
