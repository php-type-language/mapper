<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\ClassMetadata;
use TypeLang\Mapper\Registry\RegistryInterface;

final class InMemoryReader extends Reader
{
    /**
     * @var array<class-string, ClassMetadata<object>>
     */
    private array $types = [];

    public function __construct(
        private readonly ReaderInterface $delegate,
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
