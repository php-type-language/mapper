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
     * @param string $property property reference: `App\User::$id`
     * @param 'get'|'set' $hook
     */
    public static function becauseHookIsBypassed(
        string $attribute,
        string $hook,
        string $property,
        SourceInfo $source,
    ): self {
        $message = \sprintf(
            'attribute "%s" of the %s hook has no effect, the mapper does not access the property through the hook',
            $attribute,
            $hook,
        );

        return new self($property, $source, $message);
    }

    /**
     * @param string $property property reference: `App\User::$id`
     */
    public static function becauseStrictIsOnGetHook(string $attribute, string $property, SourceInfo $source): self
    {
        $message = \sprintf(
            'argument "strict" of attribute "%s" of the get hook has no effect, it applies to denormalization',
            $attribute,
        );

        return new self($property, $source, $message);
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
