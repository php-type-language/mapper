<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\PropertyWriter;

use TypeLang\Mapper\PropertyAccessor\PropertyWriterInterface;

/**
 * `$object->name = $value`, for a property writable outside the class.
 *
 * @template-implements PropertyWriterInterface<object, mixed>
 */
final class DirectPropertyWriter implements PropertyWriterInterface
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        private readonly string $property,
    ) {}

    /**
     * @throws \TypeError in case of the value does not match the type of
     *         the property
     */
    public function write(object $object, mixed $value): void
    {
        // @phpstan-ignore-next-line property.dynamicName (chosen on creation)
        $object->{$this->property} = $value;
    }
}
