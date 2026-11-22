<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor;

use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyAccessorExceptionInterface;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyNotReadableException;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyNotWritableException;
use TypeLang\Mapper\PropertyAccessor\Strategy\AccessStrategyInterface;
use TypeLang\Mapper\PropertyAccessor\Strategy\HookAccessStrategy;
use TypeLang\Mapper\PropertyAccessor\Strategy\MethodAccessStrategy;
use TypeLang\Mapper\PropertyAccessor\Strategy\StorageAccessStrategy;

/**
 * Creates the reader and the writer of a property once by the first
 * strategy that takes the access point of the direction in the metadata.
 * The default strategies, after the ones of the user, take one point each:
 * the backing store, the hook and the method.
 *
 * ```
 * $factory = PropertyAccessorFactory::createDefault([new LazyAccessStrategy()]);
 *
 * $factory->createWriter($class, $metadata)->write($user, 'user@example.com');
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
            new StorageAccessStrategy(),
            new HookAccessStrategy(),
            new MethodAccessStrategy(),
        ]);
    }

    /**
     * @param \ReflectionClass<object> $class
     * @return PropertyReaderInterface<object, mixed>
     * @throws PropertyNotReadableException in case of the property has no
     *         read direction or no strategy takes its point
     * @throws PropertyAccessorExceptionInterface in case of a strategy takes
     *         the point, but it cannot be read
     */
    public function createReader(\ReflectionClass $class, PropertyMetadata $metadata): PropertyReaderInterface
    {
        if ($metadata->read === null) {
            throw PropertyNotReadableException::becausePropertyIsWriteOnly($class->name, $metadata->name);
        }

        foreach ($this->strategies as $strategy) {
            $reader = $strategy->tryCreateReader($class, $metadata);

            if ($reader !== null) {
                return $reader;
            }
        }

        throw PropertyNotReadableException::becauseNoStrategyReadsProperty($class->name, $metadata->name);
    }

    /**
     * @param \ReflectionClass<object> $class
     * @return PropertyWriterInterface<object, mixed>
     * @throws PropertyNotWritableException in case of the property has no
     *         write direction or no strategy takes its point
     * @throws PropertyAccessorExceptionInterface in case of a strategy takes
     *         the point, but it cannot be written
     */
    public function createWriter(\ReflectionClass $class, PropertyMetadata $metadata): PropertyWriterInterface
    {
        if ($metadata->write === null) {
            throw PropertyNotWritableException::becausePropertyIsReadOnly($class->name, $metadata->name);
        }

        foreach ($this->strategies as $strategy) {
            $writer = $strategy->tryCreateWriter($class, $metadata);

            if ($writer !== null) {
                return $writer;
            }
        }

        throw PropertyNotWritableException::becauseNoStrategyWritesProperty($class->name, $metadata->name);
    }
}
