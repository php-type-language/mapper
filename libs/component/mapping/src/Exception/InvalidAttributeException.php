<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Exception;

use TypeLang\Mapper\Mapping\Metadata\SourceInfo;

/**
 * Attribute of a property, a class or a case cannot be created.
 */
final class InvalidAttributeException extends MetadataException
{
    /**
     * @param string $target property, class or case: `App\User::$id`,
     *        `App\User`, `App\Status::Active`
     */
    public static function becauseAttributeCannotBeCreated(
        string $attribute,
        string $target,
        SourceInfo $source,
        \Throwable $previous,
    ): self {
        $message = \sprintf('attribute "%s" cannot be created (%s)', $attribute, $previous->getMessage());

        return new self($target, $source, $message, $previous);
    }

    /**
     * @param string $method method reference: `App\User::getId()`
     */
    public static function becauseAttributeIsOnMethod(string $attribute, string $method, SourceInfo $source): self
    {
        $message = \sprintf('attribute "%s" is allowed only on a property or its hook', $attribute);

        return new self($method, $source, $message);
    }
}
