<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ObjectType\PropertyAccessor;

use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;

final class ReflectionPropertyAccessor implements PropertyAccessorInterface
{
    /**
     * @throws \ReflectionException
     */
    private function getProperty(object $object, PropertyMetadata $meta): \ReflectionProperty
    {
        return new \ReflectionProperty($object, $meta->getName());
    }

    public function getValue(object $object, PropertyMetadata $meta): mixed
    {
        try {
            $property = $this->getProperty($object, $meta);

            return $property->getValue($object);
        } catch (\ReflectionException) {
            return null;
        }
    }

    public function isReadable(object $object, PropertyMetadata $meta): bool
    {
        if (!\property_exists($object, $meta->getName())) {
            return false;
        }

        if (\PHP_VERSION_ID >= 80400) {
            return $this->isReadableUsingHooks($object, $meta);
        }

        return true;
    }

    private function isReadableUsingHooks(object $object, PropertyMetadata $meta): bool
    {
        $property = $this->getProperty($object, $meta);

        // @phpstan-ignore-next-line : Requires PHPStan-compatible version for PHP 8.4
        return $property->getHook(\PropertyHookType::Get) !== null
            // @phpstan-ignore-next-line : Requires PHPStan-compatible version for PHP 8.4
            || $property->getHook(\PropertyHookType::Set) === null;
    }

    public function setValue(object $object, PropertyMetadata $meta, mixed $value): void
    {
        try {
            $property = $this->getProperty($object, $meta);

            $property->setValue($object, $value);
        } catch (\ReflectionException) {
            return;
        }
    }

    public function isWritable(object $object, PropertyMetadata $meta): bool
    {
        if (\PHP_VERSION_ID >= 80400) {
            return $this->isWritableUsingHooks($object, $meta);
        }

        return true;
    }

    /**
     * @throws \ReflectionException
     */
    private function isWritableUsingHooks(object $object, PropertyMetadata $meta): bool
    {
        $property = $this->getProperty($object, $meta);

        // @phpstan-ignore-next-line : Requires PHPStan-compatible version for PHP 8.4
        return $property->getHook(\PropertyHookType::Get) === null
            // @phpstan-ignore-next-line : Requires PHPStan-compatible version for PHP 8.4
            || $property->getHook(\PropertyHookType::Set) !== null;
    }
}
