<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

abstract class TypeInfo extends MetadataInfo
{
    public function __construct(
        public ?bool $strict = null,
        ?SourceInfo $source = null,
    ) {
        parent::__construct($source);
    }
}
