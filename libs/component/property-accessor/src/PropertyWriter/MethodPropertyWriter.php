<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\PropertyWriter;

use TypeLang\Mapper\PropertyAccessor\PropertyWriterInterface;

/**
 * `$object->method($value)`, for a public setter, its result is not used.
 *
 * @template-implements PropertyWriterInterface<object, mixed>
 */
final class MethodPropertyWriter implements PropertyWriterInterface
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        private readonly string $method,
    ) {}

    /**
     * @throws \TypeError in case of the value does not match the type of
     *         the parameter
     */
    public function write(object $object, mixed $value): void
    {
        // @phpstan-ignore-next-line method.dynamicName (chosen on creation)
        $object->{$this->method}($value);
    }
}
