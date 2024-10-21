<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Runtime\Repository\Repository;

final class NullDriver implements DriverInterface
{
    public function getClassMetadata(\ReflectionClass $class, Repository $types): ClassMetadata
    {
        return new ClassMetadata($class->getName());
    }
}
