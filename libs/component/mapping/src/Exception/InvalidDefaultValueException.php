<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Exception;

use TypeLang\Mapper\Mapping\Metadata\SourceInfo;

/**
 * Default value of a property cannot be evaluated or cannot be a default
 * value of the mapping.
 */
final class InvalidDefaultValueException extends MetadataException
{
    /**
     * @param string $property property reference: `App\User::$id`
     */
    public static function becauseDefaultValueCannotBeEvaluated(
        string $property,
        SourceInfo $source,
        \Throwable $previous,
    ): self {
        $message = \sprintf('default value cannot be evaluated (%s)', $previous->getMessage());

        return new self($property, $source, $message, $previous);
    }

    /**
     * @param string $property property reference: `App\User::$id`
     * @param non-empty-string $type type of the value that cannot be a
     *        default value: an object other than an enum case
     */
    public static function becauseDefaultValueIsNotSupported(string $property, string $type, SourceInfo $source): self
    {
        $message = \sprintf('default value of type "%s" is not supported', \addcslashes($type, '"'));

        return new self($property, $source, $message);
    }
}
