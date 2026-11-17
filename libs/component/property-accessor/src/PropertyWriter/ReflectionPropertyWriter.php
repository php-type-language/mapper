<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\PropertyWriter;

use TypeLang\Mapper\PropertyAccessor\PropertyWriterInterface;

/**
 * `$reflection->setValue($object, $value)`, for a `private`, `protected`
 * or `readonly` property, a virtual one is written through its set hook.
 * Initializes a `readonly` property outside the class.
 *
 * @template-implements PropertyWriterInterface<object, mixed>
 */
final class ReflectionPropertyWriter implements PropertyWriterInterface
{
    public function __construct(
        private readonly \ReflectionProperty $property,
    ) {}

    /**
     * @throws \TypeError in case of the value does not match the type of
     *         the property
     * @throws \Error in case of the `readonly` property is already
     *         initialized
     */
    public function write(object $object, mixed $value): void
    {
        $this->property->setValue($object, $value);
    }
}
