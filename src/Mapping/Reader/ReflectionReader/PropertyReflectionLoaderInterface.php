<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\ReflectionReader;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyPrototype;

interface PropertyReflectionLoaderInterface
{
    public function load(\ReflectionProperty $property, PropertyPrototype $prototype): void;
}
