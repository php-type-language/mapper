<?php

declare(strict_types=1);

namespace Serafim\Mapper\Meta\Reader;

use Serafim\Mapper\Meta\ClassMetadata;
use Serafim\Mapper\Registry\RegistryInterface;

final class NullReader extends Reader
{
    public function getClassMetadata(\ReflectionClass $class, RegistryInterface $types): ClassMetadata
    {
        return new ClassMetadata($class->getName());
    }
}
