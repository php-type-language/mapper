<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use TypeLang\Mapper\Mapping\ClassMetadata;
use TypeLang\Mapper\Registry\RegistryInterface;

final class NullDriver extends Driver
{
    public function getClassMetadata(\ReflectionClass $class, RegistryInterface $types): ClassMetadata
    {
        return new ClassMetadata($class->getName());
    }
}
