<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Meta\Reader;

use TypeLang\Mapper\Type\Meta\TypeMetadata;
use TypeLang\Mapper\Type\TypeInterface;

interface ReaderInterface
{
    /**
     * @template T of TypeInterface
     *
     * @param \ReflectionClass<T> $class
     *
     * @return TypeMetadata<T>
     */
    public function getTypeMetadata(\ReflectionClass $class): TypeMetadata;
}
