<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyMetadata;

use TypeLang\Mapper\Mapping\Metadata\MetadataInfo;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;

final class DefaultValueInfo extends MetadataInfo
{
    public function __construct(
        public readonly mixed $value,
        ?SourceInfo $source = null,
    ) {
        parent::__construct($source);
    }
}
