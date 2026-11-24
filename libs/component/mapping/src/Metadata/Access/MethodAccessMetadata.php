<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\Access;

/**
 * Public method of the object: a getter taking no value on normalization,
 * a setter taking the value on denormalization, its result is not used.
 */
final class MethodAccessMetadata extends AccessMetadata
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $method,
    ) {}
}
