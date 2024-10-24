<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Runtime\Repository\TypeRepository;

abstract class Driver implements DriverInterface
{
    public function __construct(
        private readonly DriverInterface $delegate = new NullDriver(),
    ) {}

    public function getClassMetadata(\ReflectionClass $class, TypeRepository $types): ClassMetadata
    {
        return $this->delegate->getClassMetadata($class, $types);
    }
}
