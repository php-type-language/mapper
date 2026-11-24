<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Reflection;

/**
 * Properties of a class together with the private properties of its
 * parents: PHP reflection gives the properties visible in the class only.
 * A private property of a parent is a member of its own, its key holds the
 * class declaring it: `App\Base::$secret`.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class ClassProperties
{
    /**
     * Returns the properties of the class and the private properties of the
     * parents, a parent with a property of the same name included.
     *
     * @param \ReflectionClass<object> $class
     * @return list<\ReflectionProperty>
     */
    public static function getProperties(\ReflectionClass $class): array
    {
        $result = $class->getProperties();
        $parent = $class->getParentClass();

        while ($parent !== false) {
            foreach ($parent->getProperties(\ReflectionProperty::IS_PRIVATE) as $property) {
                if ($property->class === $parent->name) {
                    $result[] = $property;
                }
            }

            $parent = $parent->getParentClass();
        }

        return $result;
    }

    /**
     * Returns the property of the name visible in the class or, in case of
     * none, the private property of the nearest parent.
     *
     * @param \ReflectionClass<object> $class
     */
    public static function findProperty(\ReflectionClass $class, string $name): ?\ReflectionProperty
    {
        if ($class->hasProperty($name)) {
            return $class->getProperty($name);
        }

        foreach (self::getProperties($class) as $property) {
            if ($property->name === $name) {
                return $property;
            }
        }

        return null;
    }

    /**
     * Returns the key of the member of the property: its name, the name with
     * the class declaring it for a private property of a parent.
     *
     * @param \ReflectionClass<object> $class
     */
    public static function getKey(\ReflectionClass $class, \ReflectionProperty $property): string
    {
        if (self::isOfParent($class, $property)) {
            return $property->class . '::$' . $property->name;
        }

        return $property->name;
    }

    /**
     * Returns the class declaring the private property, the class of its
     * access, {@see null} for a property that is not private.
     *
     * @return class-string|null
     */
    public static function findPrivateClass(\ReflectionProperty $property): ?string
    {
        if (!$property->isPrivate()) {
            return null;
        }

        return $property->class;
    }

    /**
     * Returns whether the property is a private property of a parent.
     *
     * @param \ReflectionClass<object> $class
     */
    private static function isOfParent(\ReflectionClass $class, \ReflectionProperty $property): bool
    {
        return $property->isPrivate() && !self::isVisible($class, $property);
    }

    /**
     * @param \ReflectionClass<object> $class
     */
    private static function isVisible(\ReflectionClass $class, \ReflectionProperty $property): bool
    {
        if (!$class->hasProperty($property->name)) {
            return false;
        }

        return $class->getProperty($property->name)->class === $property->class;
    }
}
