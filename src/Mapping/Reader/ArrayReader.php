<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Metadata\ClassPrototype;
use TypeLang\Mapper\Mapping\Reader\ArrayReader\ClassArrayLoaderInterface;
use TypeLang\Mapper\Mapping\Reader\ArrayReader\PropertyArrayLoaderInterface;

/**
 * @phpstan-type PropertyConfigType array{
 *     name?: non-empty-string,
 *     type?: non-empty-string,
 *     skip?: 'null'|'empty'|non-empty-string|list<'null'|'empty'|non-empty-string>,
 *     type_error_message?: non-empty-string,
 *     undefined_error_message?: non-empty-string,
 *     ...
 * }
 * @phpstan-type ClassDiscriminatorConfigType array{
 *     field: non-empty-string,
 *     map: array<non-empty-string, non-empty-string>,
 *     otherwise?: non-empty-string,
 * }
 * @phpstan-type ClassConfigType array{
 *     normalize_as_array?: bool,
 *     discriminator?: ClassDiscriminatorConfigType,
 *     properties?: array<non-empty-string, non-empty-string|PropertyConfigType>
 * }
 *
 * @internal Not implemented yet
 */
abstract class ArrayReader extends Reader
{
    /**
     * @var list<ClassArrayLoaderInterface>
     */
    private readonly array $classLoaders;

    /**
     * @var list<PropertyArrayLoaderInterface>
     */
    private readonly array $propertyLoaders;

    public function __construct(
        ReaderInterface $delegate = new NullReader(),
    ) {
        parent::__construct($delegate);

        $this->classLoaders = $this->createClassLoaders();
        $this->propertyLoaders = $this->createPropertyLoaders();
    }

    /**
     * @return list<ClassArrayLoaderInterface>
     */
    private function createClassLoaders(): array
    {
        return [
        ];
    }

    /**
     * @return list<PropertyArrayLoaderInterface>
     */
    private function createPropertyLoaders(): array
    {
        return [
        ];
    }

    /**
     * @param \ReflectionClass<object> $class
     * @return ClassConfigType
     */
    abstract protected function load(\ReflectionClass $class): array;

    public function read(\ReflectionClass $class): ClassPrototype
    {
        $info = parent::read($class);

        // TODO

        return $info;
    }
}
