<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\ArrayReader;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyInfo;
use TypeLang\Mapper\Mapping\Reader\ArrayReader;

/**
 * @phpstan-import-type PropertyConfigType from ArrayReader
 */
interface PropertyArrayLoaderInterface
{
    /**
     * @param PropertyConfigType $config
     */
    public function load(\ReflectionProperty $property, PropertyInfo $prototype, array $config): void;
}
