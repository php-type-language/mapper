<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Meta\Reader;

use TypeLang\Mapper\Meta\ClassMetadata;
use TypeLang\Mapper\Registry\RegistryInterface;

interface ReaderInterface
{
    /**
     * @template T of object
     *
     * @param \ReflectionClass<T> $class
     *
     * @return ClassMetadata<T>
     */
    public function getClassMetadata(\ReflectionClass $class, RegistryInterface $types): ClassMetadata;
}
