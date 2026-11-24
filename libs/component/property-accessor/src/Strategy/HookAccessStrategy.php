<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Strategy;

use TypeLang\Mapper\Mapping\Metadata\Access\HookAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Field\InputFieldMetadata;
use TypeLang\Mapper\Mapping\Metadata\Field\OutputFieldMetadata;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyNotReadableException;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyNotWritableException;
use TypeLang\Mapper\PropertyAccessor\PropertyReader\DirectPropertyReader;
use TypeLang\Mapper\PropertyAccessor\PropertyReader\ReflectionPropertyReader;
use TypeLang\Mapper\PropertyAccessor\PropertyReaderInterface;
use TypeLang\Mapper\PropertyAccessor\PropertyWriter\DirectPropertyWriter;
use TypeLang\Mapper\PropertyAccessor\PropertyWriter\ReflectionPropertyWriter;
use TypeLang\Mapper\PropertyAccessor\PropertyWriterInterface;

/**
 * Takes {@see HookAccessMetadata}: calls the hook of the direction as
 * `$object->name` where it is visible, then through the reflection
 * (PHP 8.4+).
 */
final class HookAccessStrategy implements AccessStrategyInterface
{
    public function tryCreateReader(\ReflectionClass $class, OutputFieldMetadata $field): ?PropertyReaderInterface
    {
        $access = $field->access;

        if (!$access instanceof HookAccessMetadata) {
            return null;
        }

        $property = PropertyDeclaration::getProperty($class, $access->property, $access->class);

        if (!PropertyDeclaration::hasGetHook($property)) {
            throw PropertyNotReadableException::becausePropertyHasNoGetHook($property->class, $property->name);
        }

        if ($property->isPublic()) {
            return new DirectPropertyReader($access->property);
        }

        return new ReflectionPropertyReader($property);
    }

    public function tryCreateWriter(\ReflectionClass $class, InputFieldMetadata $field): ?PropertyWriterInterface
    {
        $access = $field->access;

        if (!$access instanceof HookAccessMetadata) {
            return null;
        }

        $property = PropertyDeclaration::getProperty($class, $access->property, $access->class);

        if (!PropertyDeclaration::hasSetHook($property)) {
            throw PropertyNotWritableException::becausePropertyHasNoSetHook($property->class, $property->name);
        }

        if (PropertyDeclaration::isPublicSet($property)) {
            return new DirectPropertyWriter($access->property);
        }

        return new ReflectionPropertyWriter($property);
    }
}
