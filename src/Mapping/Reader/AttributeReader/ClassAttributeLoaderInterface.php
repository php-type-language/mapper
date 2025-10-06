<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\AttributeReader;

use TypeLang\Mapper\Mapping\Metadata\ClassPrototype;

interface ClassAttributeLoaderInterface
{
    /**
     * @template T of object
     *
     * @param \ReflectionClass<T> $class
     * @param ClassPrototype<T> $prototype
     */
    public function load(\ReflectionClass $class, ClassPrototype $prototype): void;
}
