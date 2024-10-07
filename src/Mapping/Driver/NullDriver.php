<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;

final class NullDriver implements DriverInterface
{
    public function getClassMetadata(\ReflectionClass $class, RepositoryInterface $types): ClassMetadata
    {
        return new ClassMetadata($class->getName());
    }
}
