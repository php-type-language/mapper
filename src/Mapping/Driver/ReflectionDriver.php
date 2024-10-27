<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use TypeLang\Mapper\Exception\Definition\PropertyTypeNotFoundException;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Parser\Node\FullQualifiedName;
use TypeLang\Parser\Node\Identifier;
use TypeLang\Parser\Node\Name;
use TypeLang\Parser\Node\Stmt\IntersectionTypeNode;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\NullableTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Node\Stmt\UnionTypeNode;

final class ReflectionDriver extends LoadableDriver
{
    #[\Override]
    protected function load(
        \ReflectionClass $reflection,
        ClassMetadata $class,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): void {
        foreach ($reflection->getProperties() as $property) {
            if (!self::isValidProperty($property)) {
                continue;
            }

            $metadata = $class->getPropertyOrCreate($property->getName());

            $this->fillType($property, $metadata, $types);
            $this->fillDefaultValue($property, $metadata);
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return;
        }

        foreach ($constructor->getParameters() as $parameter) {
            if (!$parameter->isPromoted()) {
                continue;
            }

            $metadata = $class->getPropertyOrCreate($parameter->getName());

            if ($parameter->isDefaultValueAvailable()) {
                $metadata->setDefaultValue($parameter->getDefaultValue());
            }
        }
    }

    private function fillDefaultValue(\ReflectionProperty $property, PropertyMetadata $meta): void
    {
        if (!$property->hasDefaultValue()) {
            return;
        }

        $default = $property->getDefaultValue();

        $meta->setDefaultValue($default);
    }

    /**
     * @throws PropertyTypeNotFoundException
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    private function fillType(
        \ReflectionProperty $property,
        PropertyMetadata $meta,
        TypeRepositoryInterface $types,
    ): void {
        $statement = $this->getTypeStatement($property);

        try {
            $type = $types->getTypeByStatement($statement);
        } catch (TypeNotFoundException $e) {
            $class = $property->getDeclaringClass();

            throw PropertyTypeNotFoundException::becauseTypeOfPropertyNotDefined(
                class: $class->getName(),
                property: $property->getName(),
                type: $e->getType(),
                previous: $e,
            );
        }

        $meta->setTypeInfo(new TypeMetadata(
            type: $type,
            statement: $statement,
        ));
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function getTypeStatement(\ReflectionProperty $property): TypeStatement
    {
        $type = $property->getType();

        if ($type === null) {
            return $this->createMixedTypeStatement();
        }

        return $this->createTypeStatement($type);
    }

    private static function isValidProperty(\ReflectionProperty $property): bool
    {
        return !$property->isStatic()
            && $property->isPublic();
    }

    private function createMixedTypeStatement(): TypeStatement
    {
        return new NamedTypeNode(new Name(new Identifier('mixed')));
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function createTypeStatement(\ReflectionType $type): TypeStatement
    {
        return match (true) {
            $type instanceof \ReflectionUnionType => $this->createUnionTypeStatement($type),
            $type instanceof \ReflectionIntersectionType => $this->createIntersectionTypeStatement($type),
            $type instanceof \ReflectionNamedType => $this->createNamedTypeStatement($type),
            default => throw new \InvalidArgumentException(\sprintf(
                'Unsupported reflection type: %s',
                $type::class,
            )),
        };
    }

    private function createNamedTypeStatement(\ReflectionNamedType $type): TypeStatement
    {
        $result = $this->createNonNullNamedTypeStatement($type);

        if ($type->allowsNull() && $type->getName() !== 'null') {
            return new NullableTypeNode($result);
        }

        return $result;
    }

    private function createNonNullNamedTypeStatement(\ReflectionNamedType $type): TypeStatement
    {
        /** @var non-empty-string $literal */
        $literal = $type->getName();

        $name = new Name($literal);

        if ($type->isBuiltin() || $name->isSpecial() || $name->isBuiltin()) {
            return new NamedTypeNode($name);
        }

        return new NamedTypeNode(new FullQualifiedName($name));
    }

    /**
     * @return UnionTypeNode<TypeStatement>
     * @throws \InvalidArgumentException
     */
    private function createUnionTypeStatement(\ReflectionUnionType $type): UnionTypeNode
    {
        $children = [];

        foreach ($type->getTypes() as $child) {
            $children[] = $this->createTypeStatement($child);
        }

        return new UnionTypeNode(...$children);
    }

    /**
     * @return IntersectionTypeNode<TypeStatement>
     * @throws \InvalidArgumentException
     */
    private function createIntersectionTypeStatement(\ReflectionIntersectionType $type): IntersectionTypeNode
    {
        $children = [];

        foreach ($type->getTypes() as $child) {
            $children[] = $this->createTypeStatement($child);
        }

        return new IntersectionTypeNode(...$children);
    }
}
