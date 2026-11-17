<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Reflection;

/**
 * Finds the hooks of a property, a property has no hooks before PHP 8.4.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class PropertyHooks
{
    /**
     * Returns the `get` hook of the property, {@see null} in case of there is
     * none.
     */
    public static function findGet(\ReflectionProperty $property): ?\ReflectionMethod
    {
        if (\PHP_VERSION_ID < 80400) {
            return null;
        }

        /** @var \ReflectionMethod|null */
        // @phpstan-ignore-next-line PHP 8.4 API is unknown for PHP 8.1
        return $property->getHook(\PropertyHookType::Get);
    }

    /**
     * Returns the `set` hook of the property, {@see null} in case of there is
     * none.
     */
    public static function findSet(\ReflectionProperty $property): ?\ReflectionMethod
    {
        if (\PHP_VERSION_ID < 80400) {
            return null;
        }

        /** @var \ReflectionMethod|null */
        // @phpstan-ignore-next-line PHP 8.4 API is unknown for PHP 8.1
        return $property->getHook(\PropertyHookType::Set);
    }
}
