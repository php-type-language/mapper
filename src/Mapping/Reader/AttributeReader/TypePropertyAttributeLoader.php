<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\AttributeReader;

use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyInfo;
use TypeLang\Mapper\Mapping\Metadata\RawTypeInfo;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;

final class TypePropertyAttributeLoader extends PropertyAttributeLoader
{
    public function load(PropertyInfo $info, \ReflectionProperty $property): void
    {
        $this->loadPropertyType($property, $info);

        if (\PHP_VERSION_ID < 80400) {
            return;
        }

        $this->loadReadHookType($property, $info);
        $this->loadWriteHookType($property, $info);
    }

    private function findSourceMap(\ReflectionProperty $property): ?SourceInfo
    {
        $class = $property->getDeclaringClass();

        if ($class->isInternal()) {
            return null;
        }

        $file = $class->getFileName();
        $line = $class->getStartLine();

        if ($file === false || $line < 1) {
            return null;
        }

        return new SourceInfo($file, $line);
    }

    private function loadPropertyType(\ReflectionProperty $property, PropertyInfo $prototype): void
    {
        $attribute = $this->findPropertyAttribute($property, MapType::class);

        if ($attribute === null) {
            return;
        }

        $prototype->read = $prototype->write = new RawTypeInfo(
            definition: $attribute->type,
            strict: $attribute->strict,
            source: $this->findSourceMap($property),
        );
    }

    private function loadReadHookType(\ReflectionProperty $property, PropertyInfo $prototype): void
    {
        $hook = $property->getHook(\PropertyHookType::Get);

        if ($hook === null) {
            return;
        }

        $attribute = $this->findPropertyAttribute($hook, MapType::class);

        if ($attribute === null) {
            return;
        }

        $prototype->read = new RawTypeInfo(
            definition: $attribute->type,
        );
    }

    private function loadWriteHookType(\ReflectionProperty $property, PropertyInfo $prototype): void
    {
        $hook = $property->getHook(\PropertyHookType::Set);

        if ($hook === null) {
            return;
        }

        $attribute = $this->findPropertyAttribute($hook, MapType::class);

        if ($attribute === null) {
            return;
        }

        $prototype->write = new RawTypeInfo(
            definition: $attribute->type,
        );
    }
}
