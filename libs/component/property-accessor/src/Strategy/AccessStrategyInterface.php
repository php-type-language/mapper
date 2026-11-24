<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Strategy;

use TypeLang\Mapper\Mapping\Metadata\Field\InputFieldMetadata;
use TypeLang\Mapper\Mapping\Metadata\Field\OutputFieldMetadata;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyAccessorExceptionInterface;
use TypeLang\Mapper\PropertyAccessor\PropertyReaderInterface;
use TypeLang\Mapper\PropertyAccessor\PropertyWriterInterface;

/**
 * One way of reading and writing the value of a field, chosen by the access
 * of the field in the metadata, such as the backing store or a method of
 * the class. A strategy may take one direction only.
 *
 * ```
 * public function tryCreateReader(
 *     \ReflectionClass $class,
 *     OutputFieldMetadata $field,
 * ): ?PropertyReaderInterface {
 *     $access = $field->access;
 *
 *     if (!$access instanceof LazyAccessMetadata) {
 *         return null;
 *     }
 *
 *     return new LazyReader($access->loader);
 * }
 * ```
 */
interface AccessStrategyInterface
{
    /**
     * Returns {@see null} in case of the strategy does not take the access
     * of the field.
     *
     * @param \ReflectionClass<object> $class
     * @return PropertyReaderInterface<object, mixed>|null
     * @throws PropertyAccessorExceptionInterface in case of the strategy
     *         takes the access, but it cannot be read
     */
    public function tryCreateReader(
        \ReflectionClass $class,
        OutputFieldMetadata $field,
    ): ?PropertyReaderInterface;

    /**
     * Returns {@see null} in case of the strategy does not take the access
     * of the field.
     *
     * @param \ReflectionClass<object> $class
     * @return PropertyWriterInterface<object, mixed>|null
     * @throws PropertyAccessorExceptionInterface in case of the strategy
     *         takes the access, but it cannot be written
     */
    public function tryCreateWriter(
        \ReflectionClass $class,
        InputFieldMetadata $field,
    ): ?PropertyWriterInterface;
}
