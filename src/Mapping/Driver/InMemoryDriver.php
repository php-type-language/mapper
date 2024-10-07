<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use TypeLang\Mapper\Mapping\ClassMetadata;
use TypeLang\Mapper\Registry\RegistryInterface;

final class InMemoryDriver extends Driver
{
    /**
     * @var array<class-string, ClassMetadata<object>>
     */
    private array $types = [];

    public function __construct(
        private readonly DriverInterface $delegate,
    ) {}

    /**
     * @template T of object
     *
     * @param \ReflectionClass<T> $class
     *
     * @return ClassMetadata<T>
     */
    public function getClassMetadata(\ReflectionClass $class, RegistryInterface $types): ClassMetadata
    {
        /** @var ClassMetadata<T> */
        return $this->types[$class->getName()] ??= $this->delegate->getClassMetadata($class, $types);
    }
}
