<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\ConfigReader;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyInfo;

/**
 * @phpstan-import-type PropertyConfigType from SchemaValidator
 */
final class ErrorMessagePropertyConfigLoader extends PropertyConfigLoader
{
    public function load(PropertyInfo $info, array $config): void
    {
        $this->loadTypeErrorMessage($info, $config);
        $this->loadUndefinedErrorMessage($info, $config);
    }

    /**
     * @param PropertyConfigType $config
     */
    private function loadTypeErrorMessage(PropertyInfo $info, array $config): void
    {
        if (!isset($config['type_error_message'])) {
            return;
        }

        $info->typeErrorMessage = $config['type_error_message'];
    }

    /**
     * @param PropertyConfigType $config
     */
    private function loadUndefinedErrorMessage(PropertyInfo $info, array $config): void
    {
        if (!isset($config['undefined_error_message'])) {
            return;
        }

        $info->undefinedErrorMessage = $config['undefined_error_message'];
    }
}
