<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Runtime\Parser\TypeParserRuntimeInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryRuntimeInterface;

interface DriverInterface
{
    /**
     * @template T of object
     *
     * @param \ReflectionClass<T> $class
     *
     * @return ClassMetadata<T>
     */
    public function getClassMetadata(
        \ReflectionClass $class,
        TypeRepositoryRuntimeInterface $types,
        TypeParserRuntimeInterface $parser,
    ): ClassMetadata;
}
