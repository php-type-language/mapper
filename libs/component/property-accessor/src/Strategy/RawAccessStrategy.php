<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Strategy;

use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\PropertyAccessor\PropertyReader\RawPropertyReader;
use TypeLang\Mapper\PropertyAccessor\PropertyReaderInterface;
use TypeLang\Mapper\PropertyAccessor\PropertyWriter\RawPropertyWriter;
use TypeLang\Mapper\PropertyAccessor\PropertyWriterInterface;

/**
 * Accesses a property with a backing store bypassing its hook of the
 * direction (PHP 8.4+).
 */
final class RawAccessStrategy implements AccessStrategyInterface
{
    public function tryCreateReader(\ReflectionProperty $property, PropertyMetadata $metadata): ?PropertyReaderInterface
    {
        if (PropertyDeclaration::isVirtual($property) || !PropertyDeclaration::hasGetHook($property)) {
            return null;
        }

        return new RawPropertyReader($property);
    }

    public function tryCreateWriter(\ReflectionProperty $property, PropertyMetadata $metadata): ?PropertyWriterInterface
    {
        if (PropertyDeclaration::isVirtual($property) || !PropertyDeclaration::hasSetHook($property)) {
            return null;
        }

        return new RawPropertyWriter($property);
    }
}
