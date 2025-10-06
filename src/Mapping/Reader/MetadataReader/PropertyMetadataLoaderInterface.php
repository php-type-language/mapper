<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\MetadataReader;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyInfo;

interface PropertyMetadataLoaderInterface
{
    public function load(PropertyInfo $info, \ReflectionProperty $property): void;
}
