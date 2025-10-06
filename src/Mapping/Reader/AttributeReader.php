<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Metadata\ClassPrototype;
use TypeLang\Mapper\Mapping\Reader\AttributeReader\AliasPropertyAttributeLoader;
use TypeLang\Mapper\Mapping\Reader\AttributeReader\ClassAttributeLoaderInterface;
use TypeLang\Mapper\Mapping\Reader\AttributeReader\DiscriminatorMapClassAttributeLoader;
use TypeLang\Mapper\Mapping\Reader\AttributeReader\ErrorMessageClassAttributeLoader;
use TypeLang\Mapper\Mapping\Reader\AttributeReader\ErrorMessagePropertyAttributeLoader;
use TypeLang\Mapper\Mapping\Reader\AttributeReader\NormalizeAsArrayClassAttributeLoader;
use TypeLang\Mapper\Mapping\Reader\AttributeReader\PropertyAttributeLoaderInterface;
use TypeLang\Mapper\Mapping\Reader\AttributeReader\SkipConditionsPropertyAttributeLoader;
use TypeLang\Mapper\Mapping\Reader\AttributeReader\TypePropertyAttributeLoader;

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

    public function __construct(
        ReaderInterface $delegate = new NullReader(),
    ) {
        parent::__construct($delegate);

        $this->classLoaders = $this->createClassLoaders();
        $this->propertyLoaders = $this->createPropertyLoaders();
    }

    /**
     * @return list<ClassAttributeLoaderInterface>
     */
    private function createClassLoaders(): array
    {
        return [
            new NormalizeAsArrayClassAttributeLoader(),
            new DiscriminatorMapClassAttributeLoader(),
            new ErrorMessageClassAttributeLoader(),
        ];
    }

    /**
     * @return list<PropertyAttributeLoaderInterface>
     */
    private function createPropertyLoaders(): array
    {
        return [
            new TypePropertyAttributeLoader(),
            new AliasPropertyAttributeLoader(),
            new ErrorMessagePropertyAttributeLoader(),
            new SkipConditionsPropertyAttributeLoader(),
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
