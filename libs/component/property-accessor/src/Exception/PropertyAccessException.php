<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Exception;

abstract class PropertyAccessException extends \RuntimeException implements
    PropertyAccessorExceptionInterface
{
    public function __construct(
        /**
         * Class declaring the property
         *
         * @var class-string
         */
        public readonly string $class,
        /**
         * Name of the property
         */
        public readonly string $property,
        string $message,
    ) {
        parent::__construct($message);
    }

    /**
     * Returns the property reference for a message, such as `Foo::$bar`.
     *
     * @param class-string $class
     */
    protected static function formatProperty(string $class, string $property): string
    {
        return '"' . $class . '::$' . $property . '"';
    }
}
