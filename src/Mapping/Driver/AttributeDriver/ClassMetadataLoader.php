<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver\AttributeDriver;

abstract class ClassMetadataLoader implements ClassMetadataLoaderInterface
{
    /**
     * @template TAttribute of object
     *
     * @param \ReflectionClass<object> $class
     * @param class-string<TAttribute> $name
     *
     * @return TAttribute|null
     */
    protected function findClassAttribute(\ReflectionClass $class, string $name): ?object
    {
        $attributes = $class->getAttributes($name, \ReflectionAttribute::IS_INSTANCEOF);

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
    protected function getAllClassAttributes(\ReflectionClass $class, string $name): iterable
    {
        $attributes = $class->getAttributes($name, \ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            yield $attribute->newInstance();
        }
    }
}
