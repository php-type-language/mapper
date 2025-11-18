<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\PropertyAccessor;

/**
 * Implementing a faster accessor that doesn't take into account
 * the behavior of PHP 8.4+ property hooks
 */
final class FastPropertyAccessor implements PropertyAccessorInterface
{
    public function getValue(object $object, string $property): mixed
    {
        try {
            $reflection = new \ReflectionProperty($object, $property);

            return $reflection->getValue($object);
        } catch (\ReflectionException) {
            return null;
        }
    }

    public function isReadable(object $object, string $property): bool
    {
        return \property_exists($object, $property);
    }

    public function setValue(object $object, string $property, mixed $value): void
    {
        try {
            $reflection = (new \ReflectionProperty($object, $property))
                ->getDeclaringClass()
                ->getProperty($property);

            $reflection->setValue($object, $value);
        } catch (\ReflectionException) {
            return;
        }
    }

    public function isWritable(object $object, string $property): bool
    {
        return \property_exists($object, $property);
    }
}
