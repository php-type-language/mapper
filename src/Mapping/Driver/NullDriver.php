<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Runtime\Repository\TypeRepository;

final class NullDriver implements DriverInterface
{
    public function getClassMetadata(\ReflectionClass $class, TypeRepository $types): ClassMetadata
    {
        return new ClassMetadata($class->getName());
    }
}
