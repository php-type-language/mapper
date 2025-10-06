<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\ReflectionReader;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyMetadata\DefaultValuePrototype;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyPrototype;

final class DefaultValueReflectionLoader extends PropertyReflectionLoader
{
    public function load(\ReflectionProperty $property, PropertyPrototype $prototype): void
    {
        if (!$property->hasDefaultValue()) {
            return;
        }

        $prototype->default = new DefaultValuePrototype(
            value: $property->getDefaultValue(),
        );
    }
}
