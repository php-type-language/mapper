<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\AttributeReader;

use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyPrototype;
use TypeLang\Mapper\Mapping\Metadata\SourceMapPrototype;
use TypeLang\Mapper\Mapping\Metadata\TypePrototype;

final class TypePropertyAttributeLoader extends PropertyAttributeLoader
{
    public function load(\ReflectionProperty $property, PropertyPrototype $prototype): void
    {
        $this->loadPropertyType($property, $prototype);

        if (\PHP_VERSION_ID < 80400) {
            return;
        }

        $this->loadReadHookType($property, $prototype);
        $this->loadWriteHookType($property, $prototype);
    }

    private function findSourceMap(\ReflectionProperty $property): ?SourceMapPrototype
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

        return new SourceMapPrototype($file, $line);
    }

    private function loadPropertyType(\ReflectionProperty $property, PropertyPrototype $prototype): void
    {
        $attribute = $this->findPropertyAttribute($property, MapType::class);

        if ($attribute === null) {
            return;
        }

        $prototype->read = $prototype->write = new TypePrototype(
            definition: $attribute->type,
            source: $this->findSourceMap($property),
        );
    }

    private function loadReadHookType(\ReflectionProperty $property, PropertyPrototype $prototype): void
    {
        $hook = $property->getHook(\PropertyHookType::Get);

        if ($hook === null) {
            return;
        }

        $attribute = $this->findPropertyAttribute($hook, MapType::class);

        if ($attribute === null) {
            return;
        }

        $prototype->read = new TypePrototype(
            definition: $attribute->type,
        );
    }

    private function loadWriteHookType(\ReflectionProperty $property, PropertyPrototype $prototype): void
    {
        $hook = $property->getHook(\PropertyHookType::Set);

        if ($hook === null) {
            return;
        }

        $attribute = $this->findPropertyAttribute($hook, MapType::class);

        if ($attribute === null) {
            return;
        }

        $prototype->write = new TypePrototype(
            definition: $attribute->type,
        );
    }
}
