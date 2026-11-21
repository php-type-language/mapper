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
    public static function becausePropertyIsReadOnly(string $class, string $property): self
    {
        $message = \sprintf('Property %s is read-only', self::formatProperty($class, $property));

        return new self($class, $property, $message);
    }

    /**
     * @param class-string $class
     */
    public static function becauseNoStrategyWritesProperty(string $class, string $property): self
    {
        $message = \sprintf('No access strategy writes property %s', self::formatProperty($class, $property));

        return new self($class, $property, $message);
    }
}
