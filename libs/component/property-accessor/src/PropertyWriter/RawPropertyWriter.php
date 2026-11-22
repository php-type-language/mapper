<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\PropertyWriter;

use TypeLang\Mapper\PropertyAccessor\PropertyWriterInterface;

/**
 * `$reflection->setRawValue($object, $value)`, for a property with a
 * backing store and a set hook, the hook is bypassed (PHP 8.4+).
 *
 * @template-implements PropertyWriterInterface<object, mixed>
 */
final class RawPropertyWriter implements PropertyWriterInterface
{
    public function __construct(
        private readonly \ReflectionProperty $property,
    ) {}

    /**
     * A scalar of another type is coerced as in the coercive typing mode.
     *
     * @throws \TypeError in case of the value cannot be coerced to the type
     *         of the property
     */
    public function write(object $object, mixed $value): void
    {
        // @phpstan-ignore-next-line PHP 8.4 API, phpVersion.min is 8.1
        $this->property->setRawValue($object, $value);
    }
}
