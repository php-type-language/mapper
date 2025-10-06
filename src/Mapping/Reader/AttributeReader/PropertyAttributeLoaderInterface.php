<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\AttributeReader;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyInfo;

interface PropertyAttributeLoaderInterface
{
    public function load(\ReflectionProperty $property, PropertyInfo $prototype): void;
}
