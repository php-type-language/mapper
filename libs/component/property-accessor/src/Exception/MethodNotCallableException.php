<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Exception;

/**
 * The method of a property cannot be called as its getter or setter.
 */
final class MethodNotCallableException extends PropertyAccessException
{
    /**
     * @param class-string $class
     */
    public function __construct(
        string $class,
        string $property,
        /**
         * Name of the method
         */
        public readonly string $method,
        string $message,
    ) {
        parent::__construct($class, $property, $message);
    }

    /**
     * @param class-string $class
     */
    public static function becauseMethodDoesNotExist(string $class, string $property, string $method): self
    {
        return self::create($class, $property, $method, 'does not exist');
    }

    /**
     * @param class-string $class
     */
    public static function becauseMethodIsNotPublic(string $class, string $property, string $method): self
    {
        return self::create($class, $property, $method, 'is not public');
    }

    /**
     * @param class-string $class
     */
    public static function becauseMethodIsStatic(string $class, string $property, string $method): self
    {
        return self::create($class, $property, $method, 'is static');
    }

    /**
     * @param class-string $class
     */
    public static function becauseGetterRequiresArguments(string $class, string $property, string $method): self
    {
        return self::create($class, $property, $method, 'requires arguments, a getter takes none');
    }

    /**
     * @param class-string $class
     */
    public static function becauseSetterDoesNotTakeOneValue(string $class, string $property, string $method): self
    {
        return self::create($class, $property, $method, 'must take exactly one value as a setter');
    }

    /**
     * @param class-string $class
     */
    private static function create(string $class, string $property, string $method, string $reason): self
    {
        $message = \sprintf(
            'Method "%s::%s()" of property %s %s',
            $class,
            $method,
            self::formatProperty($class, $property),
            $reason,
        );

        return new self($class, $property, $method, $message);
    }
}
