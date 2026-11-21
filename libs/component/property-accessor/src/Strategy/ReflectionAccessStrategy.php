<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Strategy;

use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\PropertyAccessor\PropertyReader\ReflectionPropertyReader;
use TypeLang\Mapper\PropertyAccessor\PropertyReaderInterface;
use TypeLang\Mapper\PropertyAccessor\PropertyWriter\ReflectionPropertyWriter;
use TypeLang\Mapper\PropertyAccessor\PropertyWriterInterface;

/**
 * Accesses any property through the reflection bypassing its visibility,
 * a virtual property through its hook. A virtual property without the hook
 * of the direction is not taken.
 */
final class ReflectionAccessStrategy implements AccessStrategyInterface
{
    public function tryCreateReader(\ReflectionProperty $property, PropertyMetadata $metadata): ?PropertyReaderInterface
    {
        if (PropertyDeclaration::isVirtual($property) && !PropertyDeclaration::hasGetHook($property)) {
            return null;
        }

        return new ReflectionPropertyReader($property);
    }

    public function tryCreateWriter(\ReflectionProperty $property, PropertyMetadata $metadata): ?PropertyWriterInterface
    {
        if (PropertyDeclaration::isVirtual($property) && !PropertyDeclaration::hasSetHook($property)) {
            return null;
        }

        // a `readonly` property is initialized in the scope of the class
        // declaring it
        return new ReflectionPropertyWriter(
            new \ReflectionProperty($property->class, $property->name),
        );
    }
}
