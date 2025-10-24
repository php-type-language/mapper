<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\ConfigReader;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyInfo;
use TypeLang\Mapper\Mapping\Metadata\RawTypeInfo;

/**
 * @phpstan-import-type PropertyConfigType from SchemaValidator
 */
final class TypePropertyConfigLoader extends PropertyConfigLoader
{
    public function load(PropertyInfo $info, array $config): void
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

        $info->read = $info->write = new RawTypeInfo(
            definition: $config['type'],
            strict: $config['strict'] ?? null,
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

        $info->write = new RawTypeInfo(
            definition: $config['write'],
        );
    }
}
