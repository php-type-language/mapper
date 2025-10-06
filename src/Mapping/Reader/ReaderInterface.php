<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Metadata\ClassPrototype;

interface ReaderInterface
{
    /**
     * @template T of object
     *
     * @param \ReflectionClass<T> $class
     * @return ClassPrototype<T>
     */
    public function read(\ReflectionClass $class): ClassPrototype;
}
