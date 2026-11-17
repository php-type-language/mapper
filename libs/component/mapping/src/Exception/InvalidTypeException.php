<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Exception;

use TypeLang\Mapper\Mapping\Metadata\SourceInfo;

/**
 * Type of a property cannot be read by its source.
 */
final class InvalidTypeException extends MetadataException
{
    /**
     * @param string $target property or class: `App\User::$id`, `App\User`
     * @param non-empty-string $reason reason the type cannot be read
     */
    public static function becauseTypeCannotBeRead(string $target, string $reason, SourceInfo $source): self
    {
        return new self($target, $source, $reason);
    }
}
