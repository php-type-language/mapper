<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\ClassInstantiator;

use TypeLang\Mapper\Exception\Mapping\NonInstantiatableException;

interface ClassInstantiatorInterface
{
    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     * @throws NonInstantiatableException occurs in case of object is not instantiatable
     * @throws \Throwable occurs for some reason when creating an object
     */
    public function instantiate(string $class): object;
}
