<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Exception;

use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Mapper\Naming\NamingStrategyInterface;

/**
 * Value of an attribute argument or of a config key has a wrong type or
 * does not apply to its property.
 */
final class InvalidOptionValueException extends MetadataException
{
    /**
     * @param string $target property or class: `App\User::$id`, `App\User`
     */
    public static function becauseValueIsNotNonEmptyString(string $key, string $target, SourceInfo $source): self
    {
        return new self($target, $source, self::formatValue($key, 'a non-empty string'));
    }

    /**
     * @param string $target property or class: `App\User::$id`, `App\User`
     */
    public static function becauseValueIsNotVariableName(string $key, string $target, SourceInfo $source): self
    {
        return new self($target, $source, self::formatValue($key, 'a variable name other than "value"'));
    }

    /**
     * @param string $target property or class: `App\User::$id`, `App\User`
     */
    public static function becauseValueIsNotBoolean(string $key, string $target, SourceInfo $source): self
    {
        return new self($target, $source, self::formatValue($key, 'a boolean'));
    }

    /**
     * @param string $target property or class: `App\User::$id`, `App\User`
     */
    public static function becauseValueIsNotIntOrString(string $key, string $target, SourceInfo $source): self
    {
        return new self($target, $source, self::formatValue($key, 'an integer or a string'));
    }

    /**
     * @param string $target property or class: `App\User::$id`, `App\User`
     */
    public static function becauseValueIsNotMap(string $key, string $target, SourceInfo $source): self
    {
        return new self($target, $source, self::formatValue($key, 'a map'));
    }

    /**
     * @param string $target property or class: `App\User::$id`, `App\User`
     */
    public static function becauseValueIsNotNonEmptyMap(string $key, string $target, SourceInfo $source): self
    {
        return new self($target, $source, self::formatValue($key, 'a non-empty map'));
    }

    /**
     * @param string $target property or class: `App\User::$id`, `App\User`
     */
    public static function becauseValueIsNotStringOrList(string $key, string $target, SourceInfo $source): self
    {
        return new self($target, $source, self::formatValue($key, 'a string or a list'));
    }

    /**
     * @param non-empty-list<string> $values
     * @param string $target property or class: `App\User::$id`, `App\User`
     */
    public static function becauseValueIsNotOneOf(
        string $key,
        array $values,
        string $target,
        SourceInfo $source,
    ): self {
        $quoted = \array_map(static fn(string $value): string => '"' . \addcslashes($value, '"') . '"', $values);

        return new self($target, $source, self::formatValue($key, 'one of ' . \implode(', ', $quoted)));
    }

    /**
     * @param string $target property or class: `App\User::$id`, `App\User`
     */
    public static function becauseExpressionIsInvalid(
        string $expression,
        string $target,
        SourceInfo $source,
        \Throwable $previous,
    ): self {
        $message = \sprintf(
            'expression "%s" is invalid (%s)',
            \addcslashes($expression, '"'),
            $previous->getMessage(),
        );

        return new self($target, $source, $message, $previous);
    }

    /**
     * @param string $target property or class: `App\User::$id`, `App\User`
     */
    public static function becauseNamingStrategyIsUnknown(string $name, string $target, SourceInfo $source): self
    {
        $message = \sprintf(
            'naming strategy "%s" is neither a built-in one nor an instantiable class of "%s"',
            \addcslashes($name, '"'),
            NamingStrategyInterface::class,
        );

        return new self($target, $source, $message);
    }

    /**
     * @param string $target property or class: `App\User::$id`, `App\User`
     */
    public static function becauseNamingStrategyRequiresArguments(
        string $class,
        string $target,
        SourceInfo $source,
    ): self {
        $message = \sprintf(
            'naming strategy "%s" requires constructor arguments, pass its instance to the attribute',
            \addcslashes($class, '"'),
        );

        return new self($target, $source, $message);
    }

    /**
     * @param string $target property or class: `App\User::$id`, `App\User`
     */
    public static function becauseNamingStrategyCannotBeCreated(
        string $class,
        string $target,
        SourceInfo $source,
        \Throwable $previous,
    ): self {
        $message = \sprintf(
            'naming strategy "%s" cannot be created (%s)',
            \addcslashes($class, '"'),
            $previous->getMessage(),
        );

        return new self($target, $source, $message, $previous);
    }

    public static function becauseClassConfigIsNotMap(string $class, SourceInfo $source): self
    {
        return new self($class, $source, 'config must be a map');
    }

    /**
     * @param string $property property reference: `App\User::$id`
     */
    public static function becausePropertyConfigIsNotStringOrMap(string $property, SourceInfo $source): self
    {
        return new self($property, $source, 'config must be a string or a map');
    }

    /**
     * @param 'normalized'|'denormalized' $direction
     * @param string $target member reference: `App\User::$id`,
     *        `App\User::getId()`
     */
    public static function becauseDirectionIsMissing(
        string $key,
        string $direction,
        string $target,
        SourceInfo $source,
    ): self {
        $message = \sprintf(
            '"%s" is given, but the member is not %s',
            \addcslashes($key, '"'),
            $direction,
        );

        return new self($target, $source, $message);
    }

    /**
     * @param string $target member reference: `App\User::$id`
     */
    public static function becauseKeyIsNextToDirections(string $key, string $target, SourceInfo $source): self
    {
        $message = \sprintf(
            '"%s" is not allowed next to "get" or "set", give it in them',
            \addcslashes($key, '"'),
        );

        return new self($target, $source, $message);
    }

    /**
     * @param string $property property reference: `App\User::$id`
     */
    public static function becausePropertyHasNoSlot(string $key, string $property, SourceInfo $source): self
    {
        $message = \sprintf(
            '"%s" accesses the backing store, but the property is virtual: add "hook: true"',
            \addcslashes($key, '"'),
        );

        return new self($property, $source, $message);
    }

    /**
     * @param string $property property reference: `App\User::$id`
     */
    public static function becausePropertyIsVirtual(string $property, SourceInfo $source): self
    {
        return new self(
            $property,
            $source,
            'a virtual property is mapped by "get" or "set" with "hook: true"',
        );
    }

    /**
     * @param string $method method reference: `App\User::getId()`
     */
    public static function becauseMethodRequiresName(string $key, string $method, SourceInfo $source): self
    {
        $message = \sprintf('"%s" of a method requires "name"', \addcslashes($key, '"'));

        return new self($method, $source, $message);
    }

    /**
     * @param string $method method reference: `App\User::getId()`
     */
    public static function becauseMethodTakesOneDirection(string $method, SourceInfo $source): self
    {
        return new self(
            $method,
            $source,
            'the method is not both a getter and a setter, give "get" or "set"',
        );
    }

    /**
     * @param string $class class reference: `App\User`
     */
    public static function becauseMethodDoesNotExist(string $method, string $class, SourceInfo $source): self
    {
        $message = \sprintf('method "%s" does not exist', \addcslashes($method, '"'));

        return new self($class, $source, $message);
    }

    /**
     * @param string $property property reference: `App\User::$id`
     * @param 'get'|'set' $hook
     */
    public static function becausePropertyHasNoHook(
        string $key,
        string $hook,
        string $property,
        SourceInfo $source,
    ): self {
        $message = \sprintf(
            '"%s" is true, but the property has no %s hook',
            \addcslashes($key, '"'),
            $hook,
        );

        return new self($property, $source, $message);
    }

    private static function formatValue(string $key, string $expected): string
    {
        return \sprintf('value of "%s" must be %s', \addcslashes($key, '"'), $expected);
    }
}
