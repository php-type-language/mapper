<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Strategy;

/**
 * Features of the declaration of a property the default strategies choose
 * by: a virtual property, its hooks, writing it outside the class. There is
 * neither a virtual property nor a hook before PHP 8.4.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\PropertyAccessor
 */
final class PropertyDeclaration
{
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
