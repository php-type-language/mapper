<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Exception;

use TypeLang\Mapper\Mapping\Metadata\SourceInfo;

/**
 * Attribute of a member, a class or a case cannot be created or is not
 * allowed where it is given.
 */
final class InvalidAttributeException extends MetadataException
{
    /**
     * @param string $target member, class or case: `App\User::$id`,
     *        `App\User::getId()`, `App\User`, `App\Status::Active`
     */
    public static function becauseAttributeCannotBeCreated(
        string $attribute,
        string $target,
        SourceInfo $source,
        \Throwable $previous,
    ): self {
        $message = \sprintf(
            'attribute "%s" cannot be created (%s)',
            \addcslashes($attribute, '"'),
            $previous->getMessage(),
        );

        return new self($target, $source, $message, $previous);
    }

    /**
     * @param string $property property reference: `App\User::$id`
     */
    public static function becausePropertyHasNoSlot(string $attribute, string $property, SourceInfo $source): self
    {
        $message = \sprintf(
            'attribute "%s" accesses the backing store, but the property is virtual: give it on the hook',
            \addcslashes($attribute, '"'),
        );

        return new self($property, $source, $message);
    }

    /**
     * @param string $property property reference: `App\User::$id`
     * @param 'get'|'set' $hook
     */
    public static function becauseAttributeIsOnWrongHook(
        string $attribute,
        string $hook,
        string $property,
        SourceInfo $source,
    ): self {
        $message = \sprintf(
            'attribute "%s" is not allowed on the %s hook',
            \addcslashes($attribute, '"'),
            $hook,
        );

        return new self($property, $source, $message);
    }

    /**
     * @param string $property property reference: `App\User::$id`
     */
    public static function becauseAccessIsDuplicated(string $attribute, string $property, SourceInfo $source): self
    {
        $message = \sprintf(
            'attribute "%s" is given both on the property and on its hook',
            \addcslashes($attribute, '"'),
        );

        return new self($property, $source, $message);
    }

    /**
     * @param string $method method reference: `App\User::getId()`
     */
    public static function becauseMethodRequiresName(string $attribute, string $method, SourceInfo $source): self
    {
        $message = \sprintf(
            'attribute "%s" of a method requires the name of the field',
            \addcslashes($attribute, '"'),
        );

        return new self($method, $source, $message);
    }

    /**
     * @param list<class-string> $access attributes of the access given for
     *        the property
     * @param string $property property reference: `App\User::$id`
     */
    public static function becauseAttributeIsNextToAccess(
        string $attribute,
        array $access,
        string $property,
        SourceInfo $source,
    ): self {
        $message = \sprintf(
            'attribute "%s" is not allowed next to "%s", give its value in their arguments',
            \addcslashes($attribute, '"'),
            \implode('", "', \array_map(
                static fn(string $name): string => \addcslashes($name, '"'),
                $access,
            )),
        );

        return new self($property, $source, $message);
    }

    /**
     * @param string $property property reference: `App\User::$id`
     */
    public static function becausePropertyIsVirtual(string $attribute, string $property, SourceInfo $source): self
    {
        $message = \sprintf(
            'attribute "%s" has no effect, a virtual property is mapped by "MapGetter" or "MapSetter" on its hooks',
            \addcslashes($attribute, '"'),
        );

        return new self($property, $source, $message);
    }

    /**
     * @param string $property property reference: `App\User::$id`
     */
    public static function becauseReadIsOnHook(string $attribute, string $property, SourceInfo $source): self
    {
        $message = \sprintf(
            'attribute "%s" of the property has no effect, the get hook reads it: give it on the hook',
            \addcslashes($attribute, '"'),
        );

        return new self($property, $source, $message);
    }

    /**
     * @param 'normalized'|'denormalized' $direction
     * @param string $target member reference: `App\User::$id`,
     *        `App\User::getId()`
     */
    public static function becauseDirectionIsMissing(
        string $attribute,
        string $direction,
        string $target,
        SourceInfo $source,
    ): self {
        $message = \sprintf(
            'attribute "%s" is given, but the member is not %s',
            \addcslashes($attribute, '"'),
            $direction,
        );

        return new self($target, $source, $message);
    }

    /**
     * @param string $target method or property reference of the hook:
     *        `App\User::getId()`, `App\User::$id`
     */
    public static function becauseGetterIsMissing(string $attribute, string $target, SourceInfo $source): self
    {
        $message = \sprintf(
            'attribute "%s" is allowed only next to "MapGetter"',
            \addcslashes($attribute, '"'),
        );

        return new self($target, $source, $message);
    }

    /**
     * @param string $method method or hook reference: `App\User::getId()`
     */
    public static function becauseAttributeIsOnMethod(string $attribute, string $method, SourceInfo $source): self
    {
        $message = \sprintf(
            'attribute "%s" is not allowed on a method or a hook',
            \addcslashes($attribute, '"'),
        );

        return new self($method, $source, $message);
    }

    /**
     * @param string $target member, class or case: `App\User::$id`,
     *        `App\User`, `App\Status::Active`
     * @param non-empty-string $place `a class`, `a property`, `a case`
     */
    public static function becauseAttributeIsNotAllowed(
        string $attribute,
        string $place,
        string $target,
        SourceInfo $source,
    ): self {
        $message = \sprintf(
            'attribute "%s" is not allowed on %s',
            \addcslashes($attribute, '"'),
            $place,
        );

        return new self($target, $source, $message);
    }

    /**
     * @param string $property property reference: `App\User::$id`
     */
    public static function becauseArgumentsDiffer(
        string $attribute,
        string $argument,
        string $property,
        SourceInfo $source,
    ): self {
        $message = \sprintf(
            'repeated attribute "%s" gives different "%s"',
            \addcslashes($attribute, '"'),
            \addcslashes($argument, '"'),
        );

        return new self($property, $source, $message);
    }
}
