<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\ConfigReader;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyInfo;
use TypeLang\Mapper\Mapping\Metadata\TypeInfo;

/**
 * @phpstan-import-type PropertyConfigType from SchemaValidator
 */
final class TypePropertyConfigLoader extends PropertyConfigLoader
{
    public function load(\ReflectionProperty $property, PropertyInfo $info, array $config): void
    {
        $this->loadPropertyType($info, $config);
        $this->loadWritePropertyType($info, $config);
    }

    /**
     * @param PropertyConfigType $config
     */
    private function loadPropertyType(PropertyInfo $info, array $config): void
    {
        if (!isset($config['type'])) {
            return;
        }

        $info->read = $info->write = new TypeInfo(
            definition: $config['type'],
        );
    }

    /**
     * @param PropertyConfigType $config
     */
    private function loadWritePropertyType(PropertyInfo $info, array $config): void
    {
        if (!isset($config['write'])) {
            return;
        }

        $info->write = new TypeInfo(
            definition: $config['write'],
        );
    }
}
