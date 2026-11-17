<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Exception;

use TypeLang\Mapper\Mapping\Metadata\SourceInfo;

/**
 * Properties of a class share an external name.
 */
final class DuplicateExternalNameException extends MetadataException
{
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
