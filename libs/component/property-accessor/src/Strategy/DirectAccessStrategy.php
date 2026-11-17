<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Strategy;

use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\PropertyAccessor\PropertyReader\DirectPropertyReader;
use TypeLang\Mapper\PropertyAccessor\PropertyReaderInterface;
use TypeLang\Mapper\PropertyAccessor\PropertyWriter\DirectPropertyWriter;
use TypeLang\Mapper\PropertyAccessor\PropertyWriterInterface;

/**
 * Accesses a property as `$object->name`: reads a public property, a
 * virtual one through its get hook, writes a property writable outside the
 * class without a set hook.
 */
final class DirectAccessStrategy implements AccessStrategyInterface
{
    public function tryCreateReader(\ReflectionProperty $property, PropertyMetadata $metadata): ?PropertyReaderInterface
    {
        if (!$property->isPublic()) {
            return null;
        }

        if (PropertyDeclaration::isVirtual($property) && !PropertyDeclaration::hasGetHook($property)) {
            return null;
        }

        /** @var non-empty-string */
        $name = $property->name;

        return new DirectPropertyReader($name);
    }

    public function tryCreateWriter(\ReflectionProperty $property, PropertyMetadata $metadata): ?PropertyWriterInterface
    {
        if (PropertyDeclaration::hasSetHook($property) || !PropertyDeclaration::isPublicSet($property)) {
            return null;
        }

        if (PropertyDeclaration::isVirtual($property)) {
            return null;
        }

        /** @var non-empty-string */
        $name = $property->name;

        return new DirectPropertyWriter($name);
    }
}
