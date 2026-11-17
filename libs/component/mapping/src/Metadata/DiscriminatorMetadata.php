<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

/**
 * Polymorphism of a class: the field of the data holding the tag and the
 * types by their tags.
 */
final class DiscriminatorMetadata
{
    public function __construct(
        /**
         * Key of the data holding the tag
         *
         * @var non-empty-string
         */
        public readonly string $field,
        /**
         * Types by their tags
         *
         * @var non-empty-array<array-key, TypeMetadata>
         */
        public readonly array $map,
        public readonly SourceInfo $source,
        /**
         * Type of the data with no tag or with an unknown one, {@see null}
         * in case of such data is an error
         */
        public readonly ?TypeMetadata $default = null,
    ) {}
}
