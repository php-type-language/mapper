<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use TypeLang\Mapper\Exception\Definition\PropertyTypeNotFoundException;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Mapping\MapName;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\SkipWhen;
use TypeLang\Mapper\Runtime\Repository\Repository;

final class AttributeDriver extends LoadableDriver
{
    #[\Override]
    protected function load(\ReflectionClass $reflection, ClassMetadata $class, Repository $types): void
    {
        foreach ($reflection->getProperties() as $property) {
            $metadata = $class->getPropertyOrCreate($property->getName());

            // -----------------------------------------------------------------
            //  Apply property type
            // -----------------------------------------------------------------

            $attribute = $this->findPropertyAttribute($property, MapType::class);

            if ($attribute !== null) {
                $type = $this->createType($attribute->type, $property, $types);

                $metadata->setTypeInfo($type);
            }

            // -----------------------------------------------------------------
            //  Apply property name
            // -----------------------------------------------------------------

            $attribute = $this->findPropertyAttribute($property, MapName::class);

            if ($attribute !== null) {
                $metadata->setExportName($attribute->name);
            }

            // -----------------------------------------------------------------
            //  Apply skip condition
            // -----------------------------------------------------------------

            $attribute = $this->findPropertyAttribute($property, SkipWhen::class);

            if ($attribute !== null) {
                $type = $this->createType($attribute->type, $property, $types);

                $metadata->setSkipCondition($type);
            }
        }
    }

    /**
     * @param non-empty-string $type
     *
     * @throws PropertyTypeNotFoundException
     */
    private function createType(string $type, \ReflectionProperty $property, Repository $types): TypeMetadata
    {
        $statement = $types->parse($type);

        $class = $property->getDeclaringClass();

        try {
            $instance = $types->getByStatement($statement, $class);
        } catch (TypeNotFoundException $e) {
            throw PropertyTypeNotFoundException::becauseTypeOfPropertyNotDefined(
                class: $class->getName(),
                property: $property->getName(),
                type: $e->getType(),
                previous: $e,
            );
        }

        return new TypeMetadata($instance, $statement);
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
