<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

use TypeLang\Mapper\Mapping\Metadata\Metadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;

/**
 * Represents an abstraction over general information about a class.
 */
final class DiscriminatorMetadata extends Metadata
{
    public function __construct(
        /**
         * Gets discriminator field name.
         *
         * @var non-empty-string
         */
        public readonly string $field,
        /**
         * The mapping between field's value and types.
         *
         * @var non-empty-array<non-empty-string, TypeMetadata>
         */
        public readonly array $map,
        /**
         * Gets default mapping type for transformations that do not comply
         * with the specified mapping rules defined in {@see getMapping()}.
         */
        public ?TypeMetadata  $default = null,
        ?int                  $createdAt = null,
    ) {
        parent::__construct($createdAt);
    }
}
