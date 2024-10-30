<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ClassType\ClassInstantiator;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

final class ReflectionClassInstantiator implements ClassInstantiatorInterface
{
    public function instantiate(ClassMetadata $class): object
    {
        $reflection = new \ReflectionClass($class->getName());

        return $reflection->newInstanceWithoutConstructor();
    }
}
