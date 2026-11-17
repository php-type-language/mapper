<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\PropertyReader;

use TypeLang\Mapper\PropertyAccessor\PropertyReaderInterface;

/**
 * `$reflection->getRawValue($object)`, for a property with a backing store
 * and a get hook, the hook is bypassed (PHP 8.4+).
 *
 * @template-implements PropertyReaderInterface<object, mixed>
 */
final class RawPropertyReader implements PropertyReaderInterface
{
    public function __construct(
        private readonly \ReflectionProperty $property,
    ) {}

    /**
     * @throws \Error in case of the property is not initialized
     */
    public function read(object $object): mixed
    {
        // @phpstan-ignore-next-line PHP 8.4 API, phpVersion.min is 8.1
        return $this->property->getRawValue($object);
    }
}
