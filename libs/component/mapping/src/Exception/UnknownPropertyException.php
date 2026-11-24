<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Exception;

use TypeLang\Mapper\Mapping\Metadata\SourceInfo;

/**
 * Source describes a property absent from the metadata it overrides.
 */
final class UnknownPropertyException extends MetadataException
{
    /**
     * @param string $property property reference: `App\User::$id`
     */
    public static function becausePropertyDoesNotExist(string $property, SourceInfo $source): self
    {
        return new self($property, $source, 'property does not exist');
    }

    /**
     * @param string $property property reference: `App\User::$id`
     */
    public static function becausePropertyIsNotMapped(string $property, SourceInfo $source): self
    {
        return new self($property, $source, 'property is not mapped');
    }

    /**
     * @param string $property property reference: `App\User::$id`
     */
    public static function becausePropertyWithAttributeIsNotMapped(string $property, SourceInfo $source): self
    {
        $message = 'property is not mapped, give it "MapGetter" or "MapSetter" or choose it by the selector';

        return new self($property, $source, $message);
    }

    /**
     * @param string $property property reference: `App\User::$id`
     */
    public static function becausePropertyIsStatic(string $property, SourceInfo $source): self
    {
        return new self($property, $source, 'property is static and cannot be mapped');
    }
}
