<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Metadata\ClassInfo;

/**
 * @template TClassMetadataLoader of object
 * @template TPropertyMetadataLoader of object
 */
abstract class Reader implements ReaderInterface
{
    /**
     * @var list<TClassMetadataLoader>
     */
    protected readonly array $classLoaders;

    /**
     * @var list<TPropertyMetadataLoader>
     */
    protected readonly array $propertyLoaders;

    public function __construct(
        private readonly ReaderInterface $delegate = new ReflectionReader(),
    ) {
        $this->classLoaders = $this->createClassLoaders();
        $this->propertyLoaders = $this->createPropertyLoaders();
    }

    /**
     * @return list<TClassMetadataLoader>
     */
    protected function createClassLoaders(): array
    {
        return [];
    }

    /**
     * @return list<TPropertyMetadataLoader>
     */
    protected function createPropertyLoaders(): array
    {
        return [];
    }

    public function read(\ReflectionClass $class): ClassInfo
    {
        return $this->delegate->read($class);
    }
}
