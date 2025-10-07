<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\AttributeReader;

abstract class ClassAttributeLoader implements ClassAttributeLoaderInterface
{
    /**
     * @template TArgAttribute of object
     *
     * @param \ReflectionClass<object> $class
     * @param class-string<TArgAttribute> $name
     *
     * @return TArgAttribute|null
     */
    protected function findClassAttribute(\ReflectionClass $class, string $name): ?object
    {
        foreach ($this->getAllClassAttributes($class, $name) as $attribute) {
            return $attribute;
        }

        return null;
    }

    /**
     * @template TArgAttribute of object
     *
     * @param \ReflectionClass<object> $class
     * @param class-string<TArgAttribute> $name
     *
     * @return iterable<array-key, TArgAttribute>
     */
    protected function getAllClassAttributes(\ReflectionClass $class, string $name): iterable
    {
        $attributes = $class->getAttributes($name, \ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            yield $attribute->newInstance();
        }
    }
}
