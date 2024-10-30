<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ClassType\ClassInstantiator;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

interface ClassInstantiatorInterface
{
    /**
     * @template T of object
     *
     * @param ClassMetadata<T> $class
     *
     * @return T
     * @throws \Throwable occurs for some reason when creating an object
     */
    public function instantiate(ClassMetadata $class): object;
}
