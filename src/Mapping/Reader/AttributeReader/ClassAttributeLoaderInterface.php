<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\AttributeReader;

use TypeLang\Mapper\Mapping\Info\ClassInfo;

interface ClassAttributeLoaderInterface
{
    /**
     * @template T of object
     *
     * @param \ReflectionClass<T> $class
     * @param ClassInfo<T> $info
     */
    public function load(\ReflectionClass $class, ClassInfo $info): void;
}
