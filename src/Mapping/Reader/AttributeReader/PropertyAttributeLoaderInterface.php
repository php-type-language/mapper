<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\AttributeReader;

use TypeLang\Mapper\Mapping\Info\ClassInfo\PropertyInfo;

interface PropertyAttributeLoaderInterface
{
    public function load(\ReflectionProperty $property, PropertyInfo $info): void;
}
