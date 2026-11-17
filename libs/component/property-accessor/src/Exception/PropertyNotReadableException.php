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
    public static function becausePropertyIsWriteOnly(string $class, string $property): self
    {
        $message = \sprintf('Property %s is write-only', self::formatProperty($class, $property));

        return new self($class, $property, $message);
    }
}
