<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Strategy;

use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyAccessorExceptionInterface;
use TypeLang\Mapper\PropertyAccessor\PropertyReaderInterface;
use TypeLang\Mapper\PropertyAccessor\PropertyWriterInterface;

/**
 * One way of reading and writing a property, such as through a getter and
 * a setter, chosen by the declaration of the property and its metadata,
 * such as by an attribute or a config of the property. A strategy may take
 * one direction only.
 *
 * ```
 * public function tryCreateReader(\ReflectionProperty $property, PropertyMetadata $metadata): ?PropertyReaderInterface
 * {
 *     $getter = 'get' . \ucfirst($property->name);
 *
 *     if (!\method_exists($property->class, $getter)) {
 *         return null;
 *     }
 *
 *     return new GetterReader($getter);
 * }
 * ```
 */
interface AccessStrategyInterface
{
    /**
     * Returns {@see null} in case of the strategy does not read the property.
     *
     * @return PropertyReaderInterface<object, mixed>|null
     * @throws PropertyAccessorExceptionInterface in case of the strategy
     *         takes the property, but it cannot be read
     */
    public function tryCreateReader(
        \ReflectionProperty $property,
        PropertyMetadata $metadata,
    ): ?PropertyReaderInterface;

    /**
     * Returns {@see null} in case of the strategy does not write the
     * property.
     *
     * @return PropertyWriterInterface<object, mixed>|null
     * @throws PropertyAccessorExceptionInterface in case of the strategy
     *         takes the property, but it cannot be written
     */
    public function tryCreateWriter(
        \ReflectionProperty $property,
        PropertyMetadata $metadata,
    ): ?PropertyWriterInterface;
}
