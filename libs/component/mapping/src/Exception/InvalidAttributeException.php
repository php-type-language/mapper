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
    public static function becauseStrictIsOnGetter(string $attribute, string $property, SourceInfo $source): self
    {
        $message = \sprintf(
            'argument "strict" of attribute "%s" of a getter has no effect, it applies to denormalization',
            $attribute,
        );

        return new self($property, $source, $message);
    }

    /**
     * @param string $property property reference: `App\User::$id`
     */
    public static function becauseHookTakesNoPropertyName(
        string $attribute,
        string $property,
        SourceInfo $source,
    ): self {
        $message = \sprintf('attribute "%s" of a hook takes no property name', $attribute);

        return new self($property, $source, $message);
    }

    /**
     * @param string $method method reference: `App\User::getId()`
     */
    public static function becauseMethodRequiresPropertyName(
        string $attribute,
        string $method,
        SourceInfo $source,
    ): self {
        $message = \sprintf('attribute "%s" of a method requires the name of the property', $attribute);

        return new self($method, $source, $message);
    }

    /**
     * @param string $property property reference: `App\User::$id`
     * @param string $method method reference: `App\User::getId()`
     */
    public static function becauseAccessMethodIsDuplicated(
        string $attribute,
        string $property,
        string $method,
        SourceInfo $source,
    ): self {
        $message = \sprintf(
            'attribute "%s" of "%s" is the second one of the property',
            $attribute,
            \addcslashes($method, '"'),
        );

        return new self($property, $source, $message);
    }

    /**
     * @param string $property property reference: `App\User::$id`
     */
    public static function becauseDirectionIsLeftOut(
        string $attribute,
        string $only,
        string $property,
        SourceInfo $source,
    ): self {
        $message = \sprintf(
            'attribute "%s" of the property is given, but attribute "%s" leaves its direction out',
            $attribute,
            $only,
        );

        return new self($property, $source, $message);
    }

    /**
     * @param string $property property reference: `App\User::$id`
     * @param 'normalized'|'denormalized' $direction
     */
    public static function becauseDirectionIsMissing(
        string $attribute,
        string $direction,
        string $property,
        SourceInfo $source,
    ): self {
        $message = \sprintf('attribute "%s" is given, but the property is not %s', $attribute, $direction);

        return new self($property, $source, $message);
    }

    /**
     * @param string $method method reference: `App\User::getId()`
     */
    public static function becauseAttributeIsOnMethod(string $attribute, string $method, SourceInfo $source): self
    {
        $message = \sprintf(
            'attribute "%s" is allowed only on a property, its hook or its getter or setter',
            $attribute,
        );

        return new self($method, $source, $message);
    }
}
