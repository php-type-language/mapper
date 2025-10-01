<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver\DocBlockDriver;

use TypeLang\Mapper\Exception\Definition\PropertyTypeNotFoundException;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class TypePropertyDocBlockLoader extends PropertyDocBlockLoader
{
    public function __construct(
        private readonly PromotedPropertyTypeDriver $promotedProperties,
        private readonly ClassPropertyTypeDriver $classProperties,
    ) {}

    public function load(
        \ReflectionProperty $property,
        PropertyMetadata $metadata,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): void {
        $reflection = $property->getDeclaringClass();

        $statement = $this->findType($reflection, $metadata);

        if ($statement === null) {
            return;
        }

        try {
            $type = $types->getTypeByStatement($statement, $reflection);
        } catch (TypeNotFoundException $e) {
            throw PropertyTypeNotFoundException::becauseTypeOfPropertyNotDefined(
                class: $reflection->name,
                property: $metadata->name,
                type: $e->getType(),
                previous: $e,
            );
        }

        $metadata->type = new TypeMetadata(
            type: $type,
            statement: $statement,
        );
    }

    /**
     * @param \ReflectionClass<object> $class
     *
     * @throws \ReflectionException
     */
    private function findType(\ReflectionClass $class, PropertyMetadata $meta): ?TypeStatement
    {
        $property = $class->getProperty($meta->name);

        if ($property->isPromoted()) {
            return $this->promotedProperties->findType($property, $meta);
        }

        return $this->classProperties->findType($property);
    }
}
