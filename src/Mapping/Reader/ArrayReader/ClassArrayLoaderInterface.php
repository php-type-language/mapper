<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\ArrayReader;

use TypeLang\Mapper\Mapping\Metadata\ClassInfo;
use TypeLang\Mapper\Mapping\Reader\ArrayReader;

/**
 * @phpstan-import-type ClassConfigType from ArrayReader
 */
interface ClassArrayLoaderInterface
{
    /**
     * @template T of object
     *
     * @param \ReflectionClass<T> $class
     * @param ClassInfo<T> $prototype
     * @param ClassConfigType $config
     */
    public function load(\ReflectionClass $class, ClassInfo $prototype, array $config): void;
}
