<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\ClassInstantiator;

final class ReflectionClassInstantiator implements ClassInstantiatorInterface
{
    public function instantiate(string $class): object
    {
        $reflection = new \ReflectionClass($class);

        return $reflection->newInstanceWithoutConstructor();
    }
}
