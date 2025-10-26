<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Instantiator;

final class ReflectionClassInstantiator implements ClassInstantiatorInterface
{
    public function instantiate(string $class): object
    {
        $reflection = new \ReflectionClass($class);

        return $reflection->newInstanceWithoutConstructor();
    }
}
