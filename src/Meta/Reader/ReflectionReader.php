<?php

declare(strict_types=1);

namespace Serafim\Mapper\Meta\Reader;

use Serafim\Mapper\Exception\TypeNotFoundException;
use Serafim\Mapper\Meta\ClassMetadata;
use Serafim\Mapper\Meta\PropertyMetadata;
use Serafim\Mapper\Registry\RegistryInterface;
use Serafim\Mapper\Type\TypeInterface;
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

            if ($parameter?->isDefaultValueAvailable()) {
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

    /**
     * @throws ReaderExceptionInterface
     */
    private function fetchType(\ReflectionProperty $property, RegistryInterface $types): ?TypeInterface
    {
        $type = $this->properties->findPropertyType($property);

        if ($type === null) {
            return null;
        }

        try {
            return $types->get($type);
        } catch (TypeNotFoundException) {
            return null;
        }
    }

    private static function isValidProperty(\ReflectionProperty $property): bool
    {
        return !$property->isStatic()
            && $property->isPublic();
    }
}
