<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Strategy;

use TypeLang\Mapper\Mapping\Metadata\AccessPoint\HookAccessPoint;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyNotReadableException;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyNotWritableException;
use TypeLang\Mapper\PropertyAccessor\PropertyReader\DirectPropertyReader;
use TypeLang\Mapper\PropertyAccessor\PropertyReader\ReflectionPropertyReader;
use TypeLang\Mapper\PropertyAccessor\PropertyReaderInterface;
use TypeLang\Mapper\PropertyAccessor\PropertyWriter\DirectPropertyWriter;
use TypeLang\Mapper\PropertyAccessor\PropertyWriter\ReflectionPropertyWriter;
use TypeLang\Mapper\PropertyAccessor\PropertyWriterInterface;

/**
 * Takes {@see HookAccessPoint}: calls the hook of the direction as
 * `$object->name` where it is visible, then through the reflection
 * (PHP 8.4+).
 */
final class HookAccessStrategy implements AccessStrategyInterface
{
    public function tryCreateReader(\ReflectionClass $class, PropertyMetadata $metadata): ?PropertyReaderInterface
    {
        $point = $metadata->read?->point;

        if (!$point instanceof HookAccessPoint) {
            return null;
        }

        $property = PropertyDeclaration::getProperty($class, $point->property);

        if (!PropertyDeclaration::hasGetHook($property)) {
            throw PropertyNotReadableException::becausePropertyHasNoGetHook($property->class, $property->name);
        }

        if ($property->isPublic()) {
            return new DirectPropertyReader($point->property);
        }

        return new ReflectionPropertyReader($property);
    }

    public function tryCreateWriter(\ReflectionClass $class, PropertyMetadata $metadata): ?PropertyWriterInterface
    {
        $point = $metadata->write?->point;

        if (!$point instanceof HookAccessPoint) {
            return null;
        }

        $property = PropertyDeclaration::getProperty($class, $point->property);

        if (!PropertyDeclaration::hasSetHook($property)) {
            throw PropertyNotWritableException::becausePropertyHasNoSetHook($property->class, $property->name);
        }

        if (PropertyDeclaration::isPublicSet($property)) {
            return new DirectPropertyWriter($point->property);
        }

        return new ReflectionPropertyWriter($property);
    }
}
