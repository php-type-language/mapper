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
}
