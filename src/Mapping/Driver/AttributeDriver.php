<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use TypeLang\Mapper\Exception\Definition\PropertyTypeNotFoundException;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Mapping\MapProperty;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;

final class AttributeDriver extends LoadableDriver
{
    #[\Override]
    protected function load(\ReflectionClass $reflection, ClassMetadata $class, RepositoryInterface $types): void
    {
        foreach ($reflection->getProperties() as $property) {
            $attribute = $this->findPropertyAttribute(
                property: $property,
                class: MapProperty::class,
            );

            if ($attribute === null) {
                continue;
            }

            $metadata = $class->getPropertyOrCreate($property->getName());

            if ($attribute->name !== null) {
                $metadata->setExportName($attribute->name);
            }

            if ($attribute->type !== null) {
                $statement = $types->parse($attribute->type);

                try {
                    $type = $types->getByStatement($statement, $reflection);
                } catch (TypeNotFoundException $e) {
                    throw PropertyTypeNotFoundException::becauseTypeOfPropertyNotDefined(
                        class: $class->getName(),
                        property: $property->getName(),
                        type: $e->getType(),
                        previous: $e,
                    );
                }

                $metadata->setTypeInfo(new TypeMetadata(
                    type: $type,
                    statement: $statement,
                ));
            }
        }
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
