<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\ClassMetadata;
use TypeLang\Mapper\Registry\RegistryInterface;

final class NullReader extends Reader
{
    public function getClassMetadata(\ReflectionClass $class, RegistryInterface $types): ClassMetadata
    {
        return new ClassMetadata($class->getName());
    }
}
