<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor;

use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyAccessorExceptionInterface;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyNotFoundException;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyNotReadableException;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyNotWritableException;
use TypeLang\Mapper\PropertyAccessor\Strategy\AccessStrategyInterface;
use TypeLang\Mapper\PropertyAccessor\Strategy\DirectAccessStrategy;
use TypeLang\Mapper\PropertyAccessor\Strategy\RawAccessStrategy;
use TypeLang\Mapper\PropertyAccessor\Strategy\ReflectionAccessStrategy;

/**
 * Creates the reader and the writer of a property once by the first
 * strategy that takes the property. A static property is rejected, so is a
 * property no strategy takes, such as a virtual one without the hook of the
 * direction. The default strategies, after the ones of the user, bypass the
 * hook of a property with a backing store, then use `$object->name`, then
 * the reflection.
 *
 * ```
 * $factory = PropertyAccessorFactory::createDefault([new GetterSetterStrategy()]);
 *
 * $factory->createWriter($property, $metadata)->write($user, 'user@example.com');
 * ```
 */
final class PropertyAccessorFactory
{
    public function __construct(
        /**
         * @var non-empty-list<AccessStrategyInterface>
         */
        private readonly array $strategies,
    ) {}

    /**
     * @param list<AccessStrategyInterface> $strategies strategies of the
     *        user, tried before the default ones in the order of the list
     */
    public static function createDefault(array $strategies = []): self
    {
        return new self([
            ...$strategies,
            new RawAccessStrategy(),
            new DirectAccessStrategy(),
            new ReflectionAccessStrategy(),
        ]);
    }

    /**
     * @return PropertyReaderInterface<object, mixed>
     * @throws PropertyNotFoundException in case of the property is static
     * @throws PropertyNotReadableException in case of no strategy reads the
     *         property, such as a virtual one without a get hook
     * @throws PropertyAccessorExceptionInterface in case of a strategy takes
     *         the property, but it cannot be read
     */
    public function createReader(\ReflectionProperty $property, PropertyMetadata $metadata): PropertyReaderInterface
    {
        if ($property->isStatic()) {
            throw PropertyNotFoundException::becausePropertyIsStatic($property->class, $property->name);
        }

        foreach ($this->strategies as $strategy) {
            $reader = $strategy->tryCreateReader($property, $metadata);

            if ($reader !== null) {
                return $reader;
            }
        }

        throw PropertyNotReadableException::becausePropertyIsWriteOnly($property->class, $property->name);
    }

    /**
     * @return PropertyWriterInterface<object, mixed>
     * @throws PropertyNotFoundException in case of the property is static
     * @throws PropertyNotWritableException in case of no strategy writes the
     *         property, such as a virtual one without a set hook
     * @throws PropertyAccessorExceptionInterface in case of a strategy takes
     *         the property, but it cannot be written
     */
    public function createWriter(\ReflectionProperty $property, PropertyMetadata $metadata): PropertyWriterInterface
    {
        if ($property->isStatic()) {
            throw PropertyNotFoundException::becausePropertyIsStatic($property->class, $property->name);
        }

        foreach ($this->strategies as $strategy) {
            $writer = $strategy->tryCreateWriter($property, $metadata);

            if ($writer !== null) {
                return $writer;
            }
        }

        throw PropertyNotWritableException::becausePropertyIsReadOnly($property->class, $property->name);
    }
}
