<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\ReflectionReader;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyInfo;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyMetadata\DefaultValueInfo;

final class DefaultValueReflectionLoader extends PropertyReflectionLoader
{
    public function load(PropertyInfo $info, \ReflectionProperty $property): void
    {
        if (!$property->hasDefaultValue()) {
            return;
        }

        $info->default = new DefaultValueInfo(
            value: $property->getDefaultValue(),
        );
    }
}
