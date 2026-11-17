<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

/**
 * Value of a property used when mapped data has no value for it.
 */
final class DefaultValueMetadata
{
    public function __construct(
        public readonly mixed $value,
        public readonly SourceInfo $source,
    ) {}
}
