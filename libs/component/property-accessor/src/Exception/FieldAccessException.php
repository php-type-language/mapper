<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Exception;

/**
 * The access of a field of the data cannot be executed.
 */
abstract class FieldAccessException extends \RuntimeException implements
    PropertyAccessorExceptionInterface
{
    public function __construct(
        /**
         * @var class-string
         */
        public readonly string $class,
        /**
         * Name of the field in the data
         */
        public readonly string $field,
        string $message,
    ) {
        parent::__construct($message);
    }
}
