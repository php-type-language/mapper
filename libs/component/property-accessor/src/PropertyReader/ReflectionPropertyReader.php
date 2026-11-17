<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\PropertyReader;

use TypeLang\Mapper\PropertyAccessor\PropertyReaderInterface;

/**
 * `$reflection->getValue($object)`, for a `private` or `protected`
 * property.
 *
 * @template-implements PropertyReaderInterface<object, mixed>
 */
final class ReflectionPropertyReader implements PropertyReaderInterface
{
    public function __construct(
        private readonly \ReflectionProperty $property,
    ) {}

    /**
     * @throws \Error in case of the property is not initialized
     */
    public function read(object $object): mixed
    {
        return $this->property->getValue($object);
    }
}
