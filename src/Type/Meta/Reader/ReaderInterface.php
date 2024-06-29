<?php

declare(strict_types=1);

namespace Serafim\Mapper\Type\Meta\Reader;

use Serafim\Mapper\Type\Meta\TypeMetadata;
use Serafim\Mapper\Type\TypeInterface;

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
