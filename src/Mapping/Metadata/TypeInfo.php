<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

final class TypeInfo extends MetadataInfo
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $definition,
        ?SourceInfo $source = null,
    ) {
        parent::__construct($source);
    }
}
