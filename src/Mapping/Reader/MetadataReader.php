<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Metadata\ClassInfo;
use TypeLang\Mapper\Mapping\Reader\MetadataReader\ClassMetadataLoaderInterface;
use TypeLang\Mapper\Mapping\Reader\MetadataReader\PropertyMetadataLoaderInterface;

/**
 * @template TClassMetadataLoader of ClassMetadataLoaderInterface
 * @template TPropertyMetadataLoader of PropertyMetadataLoaderInterface
 * @template-extends Reader<TClassMetadataLoader, TPropertyMetadataLoader>
 */
abstract class MetadataReader extends Reader
{
    #[\Override]
    public function read(\ReflectionClass $class): ClassInfo
    {
        $classInfo = parent::read($class);

        foreach ($this->classLoaders as $classLoader) {
            $classLoader->load($classInfo, $class);
        }

        foreach ($class->getProperties() as $property) {
            if (!$this->isLoadableProperty($property)) {
                continue;
            }

            /** @phpstan-ignore-next-line : Property name cannot be empty */
            $propertyInfo = $classInfo->getPropertyOrCreate($property->name);

            foreach ($this->propertyLoaders as $propertyLoader) {
                $propertyLoader->load($propertyInfo, $property);
            }
        }

        return $classInfo;
    }

    protected function isLoadableProperty(\ReflectionProperty $property): bool
    {
        return $property->isPublic()
            && !$property->isStatic();
    }
}
