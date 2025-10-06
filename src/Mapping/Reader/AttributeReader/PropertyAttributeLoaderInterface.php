<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\AttributeReader;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyPrototype;

interface PropertyAttributeLoaderInterface
{
    public function load(\ReflectionProperty $property, PropertyPrototype $prototype): void;
}
