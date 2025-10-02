<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Info\ClassInfo;

interface ReaderInterface
{
    /**
     * @template T of object
     *
     * @param \ReflectionClass<T> $class
     * @return ClassInfo<T>
     */
    public function read(\ReflectionClass $class): ClassInfo;
}
