<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;

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
    public function getClassMetadata(\ReflectionClass $class, RepositoryInterface $types): ClassMetadata
    {
        /** @var ClassMetadata<T> */
        return $this->types[$class->getName()] ??= $this->delegate->getClassMetadata($class, $types);
    }
}
