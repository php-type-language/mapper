<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\Access;

/**
 * Backing store of a PHP property, its hooks are bypassed.
 */
final class SlotAccessMetadata extends AccessMetadata
{
    public function __construct(
        /**
         * Name of the PHP property
         *
         * @var non-empty-string
         */
        public readonly string $property,
        /**
         * Class declaring the private property, the class of the metadata
         * or its parent, {@see null} for a property that is not private
         *
         * @var class-string|null
         */
        public readonly ?string $class = null,
    ) {}
}
