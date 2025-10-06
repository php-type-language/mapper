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
     * @template T of object
     *
     * @param \ReflectionClass<T> $class
     * @param ClassInfo<T> $info
     * @param ClassConfigType $config
     */
    public function load(\ReflectionClass $class, ClassInfo $info, array $config): void;
}
