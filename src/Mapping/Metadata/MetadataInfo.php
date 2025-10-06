<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

abstract class MetadataInfo
{
    public function __construct(
        public ?SourceInfo $source = null,
    ) {}
}
