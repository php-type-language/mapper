<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\MetadataReader;

use TypeLang\Mapper\Mapping\Metadata\ClassInfo;

interface ClassMetadataLoaderInterface
{
    /**
     * @template T of object
     *
     * @param \ReflectionClass<T> $class
     * @param ClassInfo<T> $info
     */
    public function load(\ReflectionClass $class, ClassInfo $info): void;
}
