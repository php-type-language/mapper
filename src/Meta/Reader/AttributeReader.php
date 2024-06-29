<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Meta\Reader;

use TypeLang\Mapper\Attribute\MapProperty;
use TypeLang\Mapper\Meta\ClassMetadata;
use TypeLang\Mapper\Meta\PropertyMetadata;
use TypeLang\Mapper\Registry\RegistryInterface;

final class AttributeReader extends Reader
{
    public function __construct(
        private readonly ReaderInterface $delegate = new ReflectionReader(),
    ) {}

    public function getClassMetadata(\ReflectionClass $class, RegistryInterface $types): ClassMetadata
    {
        $metadata = $this->delegate->getClassMetadata($class, $types);

        foreach ($class->getProperties() as $reflection) {
            $attribute = $this->findPropertyAttribute($reflection, MapProperty::class);

            if ($attribute === null) {
                continue;
            }

            $property = $metadata->findPropertyByName($reflection->getName())
                ?? new PropertyMetadata($reflection->getName());

            if ($attribute->name !== null) {
                $property = $property->withExportName(
                    name: $attribute->name,
                );
            }

            if ($attribute->type !== null) {
                $statement = $types->parse($attribute->type);

                $property = $property->withType(
                    type: $types->get($statement),
                    statement: $statement,
                );
            }

            $metadata = $metadata->withAddedProperty($property);
        }

        return $metadata;
    }

    /**
     * @template TAttribute of object
     *
     * @param class-string<TAttribute> $class
     *
     * @return TAttribute|null
     */
    private function findPropertyAttribute(\ReflectionProperty $property, string $class): ?object
    {
        $attributes = $property->getAttributes($class, \ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            /** @var TAttribute */
            return $attribute->newInstance();
        }

        return null;
    }
}
