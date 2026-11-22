<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Exception;

/**
 * The class has no instance property with the given name.
 */
final class PropertyNotFoundException extends PropertyAccessException
{
    /**
     * @param class-string $class
     */
    public static function becausePropertyDoesNotExist(string $class, string $property): self
    {
        $message = \sprintf('Property %s does not exist', self::formatProperty($class, $property));

        return new self($class, $property, $message);
    }

    /**
     * @param class-string $class
     */
    public static function becausePropertyIsStatic(string $class, string $property): self
    {
        $message = \sprintf('Property %s is static', self::formatProperty($class, $property));

        return new self($class, $property, $message);
    }
}
