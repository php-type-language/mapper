<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\ClassInstantiator;

interface ClassInstantiatorInterface
{
    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     * @throws \Throwable occurs for some reason when creating an object
     */
    public function instantiate(string $class): object;
}
