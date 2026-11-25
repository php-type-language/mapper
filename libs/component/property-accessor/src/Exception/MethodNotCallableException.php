<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Exception;

/**
 * The method of a field cannot be called as its getter or setter.
 */
final class MethodNotCallableException extends FieldAccessException
{
    /**
     * @param class-string $class
     */
    public function __construct(
        string $class,
        string $field,
        public readonly string $method,
        string $message,
    ) {
        parent::__construct($class, $field, $message);
    }

    /**
     * @param class-string $class
     */
    public static function becauseMethodDoesNotExist(string $class, string $field, string $method): self
    {
        return self::create($class, $field, $method, 'does not exist');
    }

    /**
     * @param class-string $class
     */
    public static function becauseMethodIsNotPublic(string $class, string $field, string $method): self
    {
        return self::create($class, $field, $method, 'is not public');
    }

    /**
     * @param class-string $class
     */
    public static function becauseMethodIsStatic(string $class, string $field, string $method): self
    {
        return self::create($class, $field, $method, 'is static');
    }

    /**
     * @param class-string $class
     */
    public static function becauseGetterRequiresArguments(string $class, string $field, string $method): self
    {
        return self::create($class, $field, $method, 'requires arguments, a getter takes none');
    }

    /**
     * @param class-string $class
     */
    public static function becauseSetterDoesNotTakeOneValue(string $class, string $field, string $method): self
    {
        return self::create($class, $field, $method, 'must take exactly one value as a setter');
    }

    /**
     * @param class-string $class
     */
    private static function create(string $class, string $field, string $method, string $reason): self
    {
        $message = \sprintf(
            'Method "%s::%s()" of field "%s" %s',
            \addcslashes($class, '"'),
            \addcslashes($method, '"'),
            \addcslashes($field, '"'),
            $reason,
        );

        return new self($class, $field, $method, $message);
    }
}
