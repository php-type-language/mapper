<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\PropertyReader;

use TypeLang\Mapper\PropertyAccessor\PropertyReaderInterface;

/**
 * `$object->name`, for a public property, a virtual one is read through
 * its get hook.
 *
 * @template-implements PropertyReaderInterface<object, mixed>
 */
final class DirectPropertyReader implements PropertyReaderInterface
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        private readonly string $property,
    ) {}

    /**
     * @throws \Error in case of the property is not initialized
     */
    public function read(object $object): mixed
    {
        // @phpstan-ignore-next-line property.dynamicName (chosen on creation)
        return $object->{$this->property};
    }
}
