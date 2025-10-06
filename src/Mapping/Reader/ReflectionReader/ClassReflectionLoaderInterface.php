<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\ReflectionReader;

use TypeLang\Mapper\Mapping\Metadata\ClassInfo;

interface ClassReflectionLoaderInterface
{
    /**
     * @template T of object
     *
     * @param \ReflectionClass<T> $class
     * @param ClassInfo<T> $prototype
     */
    public function load(\ReflectionClass $class, ClassInfo $prototype): void;
}
