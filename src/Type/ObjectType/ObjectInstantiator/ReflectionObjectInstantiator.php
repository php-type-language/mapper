<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ObjectType\ObjectInstantiator;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

final class ReflectionObjectInstantiator implements ObjectInstantiatorInterface
{
    public function instantiate(ClassMetadata $class): object
    {
        $reflection = new \ReflectionClass($class->getName());

        return $reflection->newInstanceWithoutConstructor();
    }
}
