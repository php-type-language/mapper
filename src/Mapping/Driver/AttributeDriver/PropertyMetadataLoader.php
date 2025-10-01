<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver\AttributeDriver;

abstract class PropertyMetadataLoader implements PropertyMetadataLoaderInterface
{
    /**
     * @template TAttribute of object
     *
     * @param class-string<TAttribute> $name
     *
     * @return TAttribute|null
     */
    protected function findPropertyAttribute(\ReflectionProperty $property, string $name): ?object
    {
        $attributes = $property->getAttributes($name, \ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            /** @var TAttribute */
            return $attribute->newInstance();
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
    protected function getAllPropertyAttributes(\ReflectionProperty $property, string $name): iterable
    {
        $attributes = $property->getAttributes($name, \ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            yield $attribute->newInstance();
        }
    }
}
