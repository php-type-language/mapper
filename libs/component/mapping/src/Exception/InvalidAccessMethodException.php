<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Exception;

use TypeLang\Mapper\Mapping\Metadata\SourceInfo;

/**
 * Method chosen as the getter or the setter of a property cannot be one.
 */
final class InvalidAccessMethodException extends MetadataException
{
    /**
     * @param string $method method reference: `App\User::getId()`
     * @param string $property property reference: `App\User::$id`
     */
    public static function becauseMethodIsNotPublic(string $method, string $property, SourceInfo $source): self
    {
        return self::create($method, 'is not public', $property, $source);
    }

    /**
     * @param string $method method reference: `App\User::getId()`
     * @param string $property property reference: `App\User::$id`
     */
    public static function becauseMethodIsStatic(string $method, string $property, SourceInfo $source): self
    {
        return self::create($method, 'is static', $property, $source);
    }

    /**
     * @param string $method method reference: `App\User::getId()`
     * @param string $property property reference: `App\User::$id`
     */
    public static function becauseGetterRequiresArguments(string $method, string $property, SourceInfo $source): self
    {
        return self::create($method, 'requires arguments, a getter takes none', $property, $source);
    }

    /**
     * @param string $method method reference: `App\User::setId()`
     * @param string $property property reference: `App\User::$id`
     */
    public static function becauseSetterDoesNotTakeOneValue(
        string $method,
        string $property,
        SourceInfo $source,
    ): self {
        return self::create($method, 'must take exactly one value as a setter', $property, $source);
    }

    private static function create(string $method, string $reason, string $property, SourceInfo $source): self
    {
        $message = \sprintf('method "%s" %s', \addcslashes($method, '"'), $reason);

        return new self($property, $source, $message);
    }
}
