<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Exception;

/**
 * The property exists, but its value cannot be read.
 */
final class PropertyNotReadableException extends PropertyAccessException
{
    /**
     * @param class-string $class
     */
    public static function becausePropertyIsVirtual(string $class, string $property): self
    {
        $message = \sprintf('Property %s has no backing store to read', self::formatProperty($class, $property));

        return new self($class, $property, $message);
    }

    /**
     * @param class-string $class
     */
    public static function becausePropertyHasNoGetHook(string $class, string $property): self
    {
        $message = \sprintf('Property %s has no get hook', self::formatProperty($class, $property));

        return new self($class, $property, $message);
    }
}
