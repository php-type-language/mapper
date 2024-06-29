<?php

declare(strict_types=1);

namespace Serafim\Mapper\Meta\Reader;

use Serafim\Mapper\Meta\ClassMetadata;
use Serafim\Mapper\Registry\RegistryInterface;

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
