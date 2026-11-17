<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Exception;

use TypeLang\Mapper\Mapping\Metadata\SourceInfo;

/**
 * Properties of a class share a name of a field: an external name or an
 * alias.
 */
final class DuplicateExternalNameException extends MetadataException
{
    public static function becausePropertyHasNameTwice(
        string $property,
        string $class,
        string $name,
        SourceInfo $source,
    ): self {
        $message = \sprintf(
            'property "$%s" has the name "%s" more than once among its external name and aliases',
            $property,
            \addcslashes($name, '"'),
        );

        return new self($class, $source, $message);
    }

    /**
     * @param non-empty-list<string> $properties names of the properties
     */
    public static function becausePropertiesHaveSameExternalName(
        array $properties,
        string $class,
        string $name,
        SourceInfo $source,
    ): self {
        $message = \sprintf(
            'properties "$%s" have the same external name "%s"',
            \implode('", "$', $properties),
            \addcslashes($name, '"'),
        );

        return new self($class, $source, $message);
    }
}
