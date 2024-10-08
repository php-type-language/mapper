<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use TypeLang\Mapper\Exception\Definition\DefinitionException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;

/**
 * Implements each driver that can supplement or modify existing
 * metadata with new property data.
 */
abstract class LoadableDriver extends Driver
{
    /**
     * @var array<class-string, ClassMetadata<object>>
     */
    private array $metadata = [];

    /**
     * @template TArg of object
     *
     * @param \ReflectionClass<TArg> $class
     *
     * @return ClassMetadata<TArg>
     * @throws DefinitionException in case of type cannot be defined
     * @throws \Throwable in case of internal error occurred
     */
    public function getClassMetadata(\ReflectionClass $class, RepositoryInterface $types): ClassMetadata
    {
        if (isset($this->metadata[$class->getName()])) {
            /** @var ClassMetadata<TArg> */
            return $this->metadata[$class->getName()];
        }

        $this->metadata[$class->getName()] = $metadata = parent::getClassMetadata($class, $types);

        $this->load($class, $metadata, $types);

        return $metadata;
    }

    /**
     * @template TArg of object
     *
     * @param \ReflectionClass<TArg> $reflection
     * @param ClassMetadata<TArg> $class
     *
     * @throws DefinitionException in case of type cannot be defined
     * @throws \Throwable in case of internal error occurred
     */
    abstract protected function load(
        \ReflectionClass $reflection,
        ClassMetadata $class,
        RepositoryInterface $types,
    ): void;
}
