<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Meta\Reader;

use TypeLang\Mapper\Meta\ClassMetadata;
use TypeLang\Mapper\Registry\RegistryInterface;

final class NullReader extends Reader
{
    public function getClassMetadata(\ReflectionClass $class, RegistryInterface $types): ClassMetadata
    {
        return new ClassMetadata($class->getName());
    }
}
