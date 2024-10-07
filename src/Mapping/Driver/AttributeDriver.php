<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use TypeLang\Mapper\Mapping\MapProperty;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Registry\RegistryInterface;

final class AttributeDriver extends Driver
{
    public function __construct(
        private readonly DriverInterface $delegate = new ReflectionDriver(),
    ) {}

    public function getClassMetadata(\ReflectionClass $class, RegistryInterface $types): ClassMetadata
    {
        $metadata = $this->delegate->getClassMetadata($class, $types);

        foreach ($class->getProperties() as $reflection) {
            $attribute = $this->findPropertyAttribute($reflection, MapProperty::class);

            if ($attribute === null) {
                continue;
            }

            $property = $metadata->findProperty($reflection->getName())
                ?? new PropertyMetadata($reflection->getName());

            if ($attribute->name !== null) {
                $property->setExportName($attribute->name);
            }

            if ($attribute->type !== null) {
                $statement = $types->parse($attribute->type);
                $property->setType($types->get($statement));
            }

            $metadata->addProperty($property);
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
