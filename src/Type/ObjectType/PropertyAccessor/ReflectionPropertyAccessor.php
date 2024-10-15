<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ObjectType\PropertyAccessor;

use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;

final class ReflectionPropertyAccessor extends PropertyAccessor
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
            return parent::getValue($object, $meta);
        }
    }

    public function isReadable(object $object, PropertyMetadata $meta): bool
    {
        return \property_exists($object, $meta->getName())
            || $this->delegate->isReadable($object, $meta);
    }

    public function setValue(object $object, PropertyMetadata $meta, mixed $value): void
    {
        try {
            $property = $this->getProperty($object, $meta);

            $property->setValue($object, $value);
        } catch (\ReflectionException) {
            parent::setValue($object, $meta, $value);
        }
    }

    public function isWritable(object $object, PropertyMetadata $meta): bool
    {
        try {
            $property = $this->getProperty($object, $meta);

            return !$property->isReadOnly();
        } catch (\ReflectionException) {
            return parent::isWritable($object, $meta);
        }
    }
}
