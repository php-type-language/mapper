<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\ReflectionReader;

use TypeLang\Mapper\Mapping\Metadata\ClassPrototype;

interface ClassReflectionLoaderInterface
{
    /**
     * @template T of object
     *
     * @param \ReflectionClass<T> $class
     * @param ClassPrototype<T> $prototype
     */
    public function load(\ReflectionClass $class, ClassPrototype $prototype): void;
}
