<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Exception;

use TypeLang\Mapper\Mapping\Metadata\Access\AccessMetadata;

/**
 * No access strategy takes the access of a field.
 */
final class AccessNotSupportedException extends FieldAccessException
{
    /**
     * @param class-string $class
     */
    public static function becauseNoStrategyReadsField(string $class, string $field, AccessMetadata $access): self
    {
        $message = \sprintf(
            'No access strategy reads field "%s" of "%s" through "%s"',
            \addcslashes($field, '"'),
            $class,
            $access::class,
        );

        return new self($class, $field, $message);
    }

    /**
     * @param class-string $class
     */
    public static function becauseNoStrategyWritesField(string $class, string $field, AccessMetadata $access): self
    {
        $message = \sprintf(
            'No access strategy writes field "%s" of "%s" through "%s"',
            \addcslashes($field, '"'),
            $class,
            $access::class,
        );

        return new self($class, $field, $message);
    }
}
