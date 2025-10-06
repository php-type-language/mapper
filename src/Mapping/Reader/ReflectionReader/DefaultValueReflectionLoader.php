<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\ReflectionReader;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyMetadata\DefaultValueInfo;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyInfo;

final class DefaultValueReflectionLoader extends PropertyReflectionLoader
{
    public function load(\ReflectionProperty $property, PropertyInfo $prototype): void
    {
        if (!$property->hasDefaultValue()) {
            return;
        }

        $prototype->default = new DefaultValueInfo(
            value: $property->getDefaultValue(),
        );
    }
}
