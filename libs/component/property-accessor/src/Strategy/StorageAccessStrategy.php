<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Strategy;

use TypeLang\Mapper\Mapping\Metadata\AccessPoint\StorageAccessPoint;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyNotReadableException;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyNotWritableException;
use TypeLang\Mapper\PropertyAccessor\PropertyReader\DirectPropertyReader;
use TypeLang\Mapper\PropertyAccessor\PropertyReader\RawPropertyReader;
use TypeLang\Mapper\PropertyAccessor\PropertyReader\ReflectionPropertyReader;
use TypeLang\Mapper\PropertyAccessor\PropertyReaderInterface;
use TypeLang\Mapper\PropertyAccessor\PropertyWriter\DirectPropertyWriter;
use TypeLang\Mapper\PropertyAccessor\PropertyWriter\RawPropertyWriter;
use TypeLang\Mapper\PropertyAccessor\PropertyWriter\ReflectionPropertyWriter;
use TypeLang\Mapper\PropertyAccessor\PropertyWriterInterface;

/**
 * Takes {@see StorageAccessPoint}: accesses the backing store of the
 * property bypassing its hook of the direction, then as `$object->name`
 * where it is visible, then through the reflection, such as a `readonly`
 * or a `private(set)` property.
 */
final class StorageAccessStrategy implements AccessStrategyInterface
{
    public function tryCreateReader(\ReflectionClass $class, PropertyMetadata $metadata): ?PropertyReaderInterface
    {
        $point = $metadata->read?->point;

        if (!$point instanceof StorageAccessPoint) {
            return null;
        }

        $property = PropertyDeclaration::getProperty($class, $point->property);

        if (PropertyDeclaration::isVirtual($property)) {
            throw PropertyNotReadableException::becausePropertyIsVirtual($property->class, $property->name);
        }

        if (PropertyDeclaration::hasGetHook($property)) {
            return new RawPropertyReader($property);
        }

        if ($property->isPublic()) {
            return new DirectPropertyReader($point->property);
        }

        return new ReflectionPropertyReader($property);
    }

    public function tryCreateWriter(\ReflectionClass $class, PropertyMetadata $metadata): ?PropertyWriterInterface
    {
        $point = $metadata->write?->point;

        if (!$point instanceof StorageAccessPoint) {
            return null;
        }

        $property = PropertyDeclaration::getProperty($class, $point->property);

        if (PropertyDeclaration::isVirtual($property)) {
            throw PropertyNotWritableException::becausePropertyIsVirtual($property->class, $property->name);
        }

        if (PropertyDeclaration::hasSetHook($property)) {
            return new RawPropertyWriter($property);
        }

        if (PropertyDeclaration::isPublicSet($property)) {
            return new DirectPropertyWriter($point->property);
        }

        // a `readonly` property is initialized in the scope of the class
        // declaring it
        return new ReflectionPropertyWriter(
            new \ReflectionProperty($property->class, $property->name),
        );
    }
}
