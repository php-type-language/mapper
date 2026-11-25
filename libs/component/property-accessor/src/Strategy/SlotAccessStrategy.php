<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Strategy;

use TypeLang\Mapper\Mapping\Metadata\Access\SlotAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Field\InputFieldMetadata;
use TypeLang\Mapper\Mapping\Metadata\Field\OutputFieldMetadata;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyNotFoundException;
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
 * Takes {@see SlotAccessMetadata}: accesses the backing store of the
 * property bypassing its hook of the direction, then as `$object->name`
 * where it is visible, then through the reflection, such as a `readonly`
 * or a `private(set)` property.
 */
final class SlotAccessStrategy implements AccessStrategyInterface
{
    /**
     * @throws PropertyNotFoundException in case of the class has no property
     *         of the access or it is static
     * @throws PropertyNotReadableException in case of the property is
     *         virtual
     */
    public function tryCreateReader(\ReflectionClass $class, OutputFieldMetadata $field): ?PropertyReaderInterface
    {
        $access = $field->access;

        if (!$access instanceof SlotAccessMetadata) {
            return null;
        }

        $property = PropertyDeclaration::getProperty($class, $access->property, $access->class);

        if (PropertyDeclaration::isVirtual($property)) {
            throw PropertyNotReadableException::becausePropertyIsVirtual($property->class, $property->name);
        }

        if (PropertyDeclaration::hasGetHook($property)) {
            return new RawPropertyReader($property);
        }

        if ($property->isPublic()) {
            return new DirectPropertyReader($access->property);
        }

        return new ReflectionPropertyReader($property);
    }

    /**
     * @throws PropertyNotFoundException in case of the class has no property
     *         of the access or it is static
     * @throws PropertyNotWritableException in case of the property is
     *         virtual
     */
    public function tryCreateWriter(\ReflectionClass $class, InputFieldMetadata $field): ?PropertyWriterInterface
    {
        $access = $field->access;

        if (!$access instanceof SlotAccessMetadata) {
            return null;
        }

        $property = PropertyDeclaration::getProperty($class, $access->property, $access->class);

        if (PropertyDeclaration::isVirtual($property)) {
            throw PropertyNotWritableException::becausePropertyIsVirtual($property->class, $property->name);
        }

        if (PropertyDeclaration::hasSetHook($property)) {
            return new RawPropertyWriter($property);
        }

        if (PropertyDeclaration::isPublicSet($property)) {
            return new DirectPropertyWriter($access->property);
        }

        // a `readonly` property is initialized in the scope of the class
        // declaring it
        return new ReflectionPropertyWriter(
            new \ReflectionProperty($property->class, $property->name),
        );
    }
}
