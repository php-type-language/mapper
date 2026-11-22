<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\PropertyReader;

use TypeLang\Mapper\PropertyAccessor\PropertyReaderInterface;

/**
 * `$object->method()`, for a public getter.
 *
 * @template-implements PropertyReaderInterface<object, mixed>
 */
final class MethodPropertyReader implements PropertyReaderInterface
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        private readonly string $method,
    ) {}

    public function read(object $object): mixed
    {
        // @phpstan-ignore-next-line method.dynamicName (chosen on creation)
        return $object->{$this->method}();
    }
}
