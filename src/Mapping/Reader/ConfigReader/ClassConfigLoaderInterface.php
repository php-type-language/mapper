<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\ConfigReader;

use TypeLang\Mapper\Mapping\Metadata\ClassInfo;

/**
 * @phpstan-import-type ClassConfigType from SchemaValidator
 */
interface ClassConfigLoaderInterface
{
    /**
     * @param ClassInfo<object> $info
     * @param ClassConfigType $config
     */
    public function load(ClassInfo $info, array $config): void;
}
