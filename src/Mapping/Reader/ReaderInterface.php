<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Metadata\ClassInfo;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;

interface ReaderInterface
{
    /**
     * @template T of object
     *
     * @param \ReflectionClass<T> $class
     *
     * @return ClassInfo<T>
     * @throws \Throwable
     */
    public function read(\ReflectionClass $class, TypeParserInterface $parser): ClassInfo;
}
