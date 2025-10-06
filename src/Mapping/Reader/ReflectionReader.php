<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Metadata\ClassPrototype;
use TypeLang\Mapper\Mapping\Reader\ReflectionReader\ClassReflectionLoaderInterface;
use TypeLang\Mapper\Mapping\Reader\ReflectionReader\DefaultValueReflectionLoader;
use TypeLang\Mapper\Mapping\Reader\ReflectionReader\PropertyReflectionLoaderInterface;
use TypeLang\Mapper\Mapping\Reader\ReflectionReader\TypePropertyReflectionLoader;

final class ReflectionReader extends Reader
{
    /**
     * @var list<ClassReflectionLoaderInterface>
     */
    private readonly array $classLoaders;

    /**
     * @var list<PropertyReflectionLoaderInterface>
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
     * @return list<ClassReflectionLoaderInterface>
     */
    private function createClassLoaders(): array
    {
        return [
        ];
    }

    /**
     * @return list<PropertyReflectionLoaderInterface>
     */
    private function createPropertyLoaders(): array
    {
        return [
            new TypePropertyReflectionLoader(),
            new DefaultValueReflectionLoader(),
        ];
    }

    #[\Override]
    public function read(\ReflectionClass $class): ClassPrototype
    {
        $classInfo = parent::read($class);

        foreach ($this->classLoaders as $classLoader) {
            $classLoader->load($class, $classInfo);
        }

        foreach ($class->getProperties() as $property) {
            if (!$this->isLoadableProperty($property)) {
                continue;
            }

            /** @phpstan-ignore-next-line : Property name cannot be empty */
            $propertyInfo = $classInfo->properties->getOrCreate($property->name);

            foreach ($this->propertyLoaders as $propertyLoader) {
                $propertyLoader->load($property, $propertyInfo);
            }
        }

        return $classInfo;
    }

    private function isLoadableProperty(\ReflectionProperty $property): bool
    {
        return $property->isPublic()
            && !$property->isStatic();
    }
}
