<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Meta\Reader;

use TypeLang\Mapper\Exception\TypeNotFoundException;
use TypeLang\Mapper\Meta\ClassMetadata;
use TypeLang\Mapper\Meta\PropertyMetadata;
use TypeLang\Mapper\Registry\RegistryInterface;
use TypeLang\Reader\Exception\ReaderExceptionInterface;
use TypeLang\Reader\PropertyReaderInterface as PropertyTypeReaderInterface;
use TypeLang\Reader\ReflectionReader as ReflectionTypeReader;

final class ReflectionReader extends Reader
{
    private PropertyTypeReaderInterface $properties;

    public function __construct(
        private readonly ReaderInterface $delegate = new NullReader(),
    ) {
        $this->properties = new ReflectionTypeReader();
    }

    /**
     * @throws ReaderExceptionInterface
     */
    public function getClassMetadata(\ReflectionClass $class, RegistryInterface $types): ClassMetadata
    {
        $metadata = $this->delegate->getClassMetadata($class, $types);

        foreach ($class->getProperties() as $property) {
            if (!self::isValidProperty($property)) {
                continue;
            }

            $metadata = $metadata->withAddedProperty(
                property: $this->getPropertyMetadataForContext(
                    metadata: $metadata->findPropertyByName($property->getName())
                        ?? new PropertyMetadata($property->getName()),
                    class: $class,
                    property: $property,
                    types: $types,
                ),
            );
        }

        return $metadata;
    }

    /**
     * @param \ReflectionClass<object> $class
     * @throws ReaderExceptionInterface
     */
    private function getPropertyMetadataForContext(
        PropertyMetadata $metadata,
        \ReflectionClass $class,
        \ReflectionProperty $property,
        RegistryInterface $types,
    ): PropertyMetadata {
        if ($property->hasDefaultValue()) {
            $metadata = $metadata->withDefaultValue($property->getDefaultValue());
        }

        if ($property->isPromoted()) {
            $parameter = $this->findParameter($class, $property);

            if ($parameter?->isDefaultValueAvailable() === true) {
                $metadata = $metadata->withDefaultValue($parameter->getDefaultValue());
            }
        }

        $statement = $this->properties->findPropertyType($property);

        if ($statement !== null) {
            try {
                $metadata = $metadata->withType($types->get($statement), $statement);
            } catch (TypeNotFoundException) {
                $metadata = $metadata->withTypeStatement($statement);
            }
        }

        return $metadata;
    }

    /**
     * @param \ReflectionClass<object> $class
     */
    private function findParameter(\ReflectionClass $class, \ReflectionProperty $property): ?\ReflectionParameter
    {
        $constructor = $class->getConstructor();

        if ($constructor === null) {
            return null;
        }

        foreach ($constructor->getParameters() as $parameter) {
            if ($parameter->getName() === $property->getName()) {
                return $parameter;
            }
        }

        return null;
    }

    private static function isValidProperty(\ReflectionProperty $property): bool
    {
        return !$property->isStatic()
            && $property->isPublic();
    }
}
