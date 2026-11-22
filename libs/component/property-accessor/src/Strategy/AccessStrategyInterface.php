<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Strategy;

use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyAccessorExceptionInterface;
use TypeLang\Mapper\PropertyAccessor\PropertyReaderInterface;
use TypeLang\Mapper\PropertyAccessor\PropertyWriterInterface;

/**
 * One way of reading and writing a property, chosen by the access point of
 * the direction in the metadata, such as the backing store or a method of
 * the class. A strategy may take one direction only.
 *
 * ```
 * public function tryCreateReader(
 *     \ReflectionClass $class,
 *     PropertyMetadata $metadata,
 * ): ?PropertyReaderInterface {
 *     $point = $metadata->read?->point;
 *
 *     if (!$point instanceof LazyAccessPoint) {
 *         return null;
 *     }
 *
 *     return new LazyReader($point->loader);
 * }
 * ```
 */
interface AccessStrategyInterface
{
    /**
     * Returns {@see null} in case of the strategy does not take the access
     * point of the read direction.
     *
     * @param \ReflectionClass<object> $class
     * @return PropertyReaderInterface<object, mixed>|null
     * @throws PropertyAccessorExceptionInterface in case of the strategy
     *         takes the point, but it cannot be read
     */
    public function tryCreateReader(
        \ReflectionClass $class,
        PropertyMetadata $metadata,
    ): ?PropertyReaderInterface;

    /**
     * Returns {@see null} in case of the strategy does not take the access
     * point of the write direction.
     *
     * @param \ReflectionClass<object> $class
     * @return PropertyWriterInterface<object, mixed>|null
     * @throws PropertyAccessorExceptionInterface in case of the strategy
     *         takes the point, but it cannot be written
     */
    public function tryCreateWriter(
        \ReflectionClass $class,
        PropertyMetadata $metadata,
    ): ?PropertyWriterInterface;
}
