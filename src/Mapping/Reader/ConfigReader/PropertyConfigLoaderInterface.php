<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\ConfigReader;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyInfo;

/**
 * @phpstan-import-type PropertyConfigType from SchemaValidator
 */
interface PropertyConfigLoaderInterface
{
    /**
     * @param PropertyConfigType $config
     */
    public function load(PropertyInfo $info, array $config): void;
}
