<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor;

use TypeLang\Mapper\Mapping\Metadata\Field\InputFieldMetadata;
use TypeLang\Mapper\Mapping\Metadata\Field\OutputFieldMetadata;
use TypeLang\Mapper\PropertyAccessor\Exception\AccessNotSupportedException;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyAccessorExceptionInterface;
use TypeLang\Mapper\PropertyAccessor\Strategy\AccessStrategyInterface;
use TypeLang\Mapper\PropertyAccessor\Strategy\HookAccessStrategy;
use TypeLang\Mapper\PropertyAccessor\Strategy\MethodAccessStrategy;
use TypeLang\Mapper\PropertyAccessor\Strategy\SlotAccessStrategy;

/**
 * Creates the reader of a field of the normalization and the writer of a
 * field of the denormalization once, by the first strategy that takes the
 * access of the field. The default strategies, after the ones of the user,
 * take one access each: the backing store, the hook and the method.
 *
 * ```
 * $factory = PropertyAccessorFactory::createDefault([
 *     new LazyAccessStrategy(),
 * ]);
 *
 * $factory->createWriter($class, $field)->write($user, 'user@example.com');
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
            new SlotAccessStrategy(),
            new HookAccessStrategy(),
            new MethodAccessStrategy(),
        ]);
    }

    /**
     * @param \ReflectionClass<object> $class
     * @return PropertyReaderInterface<object, mixed>
     * @throws AccessNotSupportedException in case of no strategy takes the
     *         access of the field
     * @throws PropertyAccessorExceptionInterface in case of a strategy takes
     *         the access, but it cannot be read
     */
    public function createReader(\ReflectionClass $class, OutputFieldMetadata $field): PropertyReaderInterface
    {
        foreach ($this->strategies as $strategy) {
            $reader = $strategy->tryCreateReader($class, $field);

            if ($reader !== null) {
                return $reader;
            }
        }

        throw AccessNotSupportedException::becauseNoStrategyReadsField($class->name, $field->name, $field->access);
    }

    /**
     * @param \ReflectionClass<object> $class
     * @return PropertyWriterInterface<object, mixed>
     * @throws AccessNotSupportedException in case of no strategy takes the
     *         access of the field
     * @throws PropertyAccessorExceptionInterface in case of a strategy takes
     *         the access, but it cannot be written
     */
    public function createWriter(\ReflectionClass $class, InputFieldMetadata $field): PropertyWriterInterface
    {
        foreach ($this->strategies as $strategy) {
            $writer = $strategy->tryCreateWriter($class, $field);

            if ($writer !== null) {
                return $writer;
            }
        }

        throw AccessNotSupportedException::becauseNoStrategyWritesField($class->name, $field->name, $field->access);
    }
}
