<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Info\ClassInfo;
use TypeLang\Mapper\Mapping\Reader\AttributeReader\ClassAttributeLoaderInterface;
use TypeLang\Mapper\Mapping\Reader\AttributeReader\PropertyAttributeLoaderInterface;

final class AttributeReader extends Reader
{
    /**
     * @var list<ClassAttributeLoaderInterface>
     */
    private readonly array $classLoaders;

    /**
     * @var list<PropertyAttributeLoaderInterface>
     */
    private readonly array $propertyLoaders;

    public function __construct(ReaderInterface $delegate = new NullReader())
    {
        parent::__construct($delegate);

        $this->classLoaders = $this->createClassLoaders();
        $this->propertyLoaders = $this->createPropertyLoaders();
    }

    /**
     * @return list<ClassAttributeLoaderInterface>
     */
    private function createClassLoaders(): array
    {
        return [];
    }

    /**
     * @return list<PropertyAttributeLoaderInterface>
     */
    private function createPropertyLoaders(): array
    {
        return [];
    }

    #[\Override]
    public function read(\ReflectionClass $class): ClassInfo
    {
        $classMetadata = parent::read($class);

        foreach ($this->classLoaders as $classLoader) {
            $classLoader->load($class, $classMetadata);
        }

        foreach ($class->getProperties() as $property) {
            $propertyMetadata = $classMetadata->properties->getOrCreate($property->name);

            foreach ($this->propertyLoaders as $propertyLoader) {
                $propertyLoader->load($property, $propertyMetadata);
            }
        }
    }
}
