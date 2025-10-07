<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\ReflectionReader;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyInfo;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyMetadata\DefaultValueInfo;

final class DefaultValuePropertyReflectionLoader extends PropertyReflectionLoader
{
    public function load(PropertyInfo $info, \ReflectionProperty $property): void
    {
        $this->loadPropertyDefaultValue($info, $property);
        $this->loadPromotedPropertyDefaultValue($info, $property);
    }

    private function loadPromotedPropertyDefaultValue(PropertyInfo $info, \ReflectionProperty $property): void
    {
        if (!$property->isPromoted()) {
            return;
        }

        $parameter = $this->findParameterByProperty($property);

        if ($parameter === null) {
            return;
        }

        if (!$parameter->isDefaultValueAvailable()) {
            return;
        }

        $info->default = new DefaultValueInfo(
            value: $parameter->getDefaultValue(),
        );
    }

    private function findParameterByProperty(\ReflectionProperty $property): ?\ReflectionParameter
    {
        $class = $property->getDeclaringClass();
        $constructor = $class->getConstructor();

        if ($constructor === null) {
            return null;
        }

        foreach ($constructor->getParameters() as $parameter) {
            // Skip non-promoted properties
            if (!$parameter->isPromoted()) {
                continue;
            }

            if ($parameter->name === $property->name) {
                return $parameter;
            }
        }

        return null;
    }

    private function loadPropertyDefaultValue(PropertyInfo $info, \ReflectionProperty $property): void
    {
        if (!$property->hasDefaultValue()) {
            $this->loadPromotedPropertyDefaultValue($info, $property);

            return;
        }

        $info->default = new DefaultValueInfo(
            value: $property->getDefaultValue(),
        );
    }
}
