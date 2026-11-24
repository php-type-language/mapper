<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Exception;

/**
 * The property exists, but a value cannot be written to it.
 */
final class PropertyNotWritableException extends PropertyAccessException
{
    /**
     * @param class-string $class
     */
    public static function becausePropertyIsVirtual(string $class, string $property): self
    {
        $message = \sprintf('Property %s has no backing store to write', self::formatProperty($class, $property));

        return new self($class, $property, $message);
    }

    /**
     * @param class-string $class
     */
    public static function becausePropertyHasNoSetHook(string $class, string $property): self
    {
        $message = \sprintf('Property %s has no set hook', self::formatProperty($class, $property));

        return new self($class, $property, $message);
    }
}
