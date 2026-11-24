<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Strategy;

use TypeLang\Mapper\PropertyAccessor\Exception\PropertyNotFoundException;

/**
 * Features of the declaration of a property the default strategies choose
 * by: the property of an access, a virtual property, its hooks,
 * writing it outside the class. There is neither a virtual property nor a
 * hook before PHP 8.4.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\PropertyAccessor
 */
final class PropertyDeclaration
{
    /**
     * @param \ReflectionClass<object> $class
     * @param class-string|null $parent class declaring the private
     *        property, the class itself or its parent
     * @throws PropertyNotFoundException in case of the class has no property
     *         of the name or it is static
     */
    public static function getProperty(
        \ReflectionClass $class,
        string $name,
        ?string $parent = null,
    ): \ReflectionProperty {
        $property = self::findProperty($class, $name, $parent);

        if ($property === null) {
            throw PropertyNotFoundException::becausePropertyDoesNotExist($parent ?? $class->name, $name);
        }

        if ($property->isStatic()) {
            throw PropertyNotFoundException::becausePropertyIsStatic($property->class, $property->name);
        }

        return $property;
    }

    /**
     * @param \ReflectionClass<object> $class
     * @param class-string|null $parent
     */
    private static function findProperty(\ReflectionClass $class, string $name, ?string $parent): ?\ReflectionProperty
    {
        if ($parent === null) {
            if (!$class->hasProperty($name)) {
                return null;
            }

            return $class->getProperty($name);
        }

        if ($class->name !== $parent && !$class->isSubclassOf($parent)) {
            return null;
        }

        if (!\property_exists($parent, $name)) {
            return null;
        }

        $property = new \ReflectionProperty($parent, $name);

        if ($property->class !== $parent || !$property->isPrivate()) {
            return null;
        }

        return $property;
    }

    public static function isVirtual(\ReflectionProperty $property): bool
    {
        if (\PHP_VERSION_ID < 80400) {
            return false;
        }

        // @phpstan-ignore-next-line PHP 8.4 API, phpVersion.min is 8.1
        return $property->isVirtual();
    }

    public static function hasGetHook(\ReflectionProperty $property): bool
    {
        if (\PHP_VERSION_ID < 80400) {
            return false;
        }

        // @phpstan-ignore-next-line PHP 8.4 API, phpVersion.min is 8.1
        return $property->hasHook(\PropertyHookType::Get);
    }

    public static function hasSetHook(\ReflectionProperty $property): bool
    {
        if (\PHP_VERSION_ID < 80400) {
            return false;
        }

        // @phpstan-ignore-next-line PHP 8.4 API, phpVersion.min is 8.1
        return $property->hasHook(\PropertyHookType::Set);
    }

    /**
     * Returns {@see true} in case of a value can be assigned to the
     * property outside the class.
     */
    public static function isPublicSet(\ReflectionProperty $property): bool
    {
        if (!$property->isPublic() || $property->isReadOnly()) {
            return false;
        }

        if (\PHP_VERSION_ID < 80400) {
            return true;
        }

        // @phpstan-ignore-next-line PHP 8.4 API, phpVersion.min is 8.1
        return !$property->isPrivateSet() && !$property->isProtectedSet();
    }
}
