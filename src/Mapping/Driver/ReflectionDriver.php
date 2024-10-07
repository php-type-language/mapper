<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
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
    protected function load(\ReflectionClass $reflection, ClassMetadata $class, RepositoryInterface $types): void
    {
        foreach ($reflection->getProperties() as $property) {
            if (!self::isValidProperty($property)) {
                continue;
            }

            $metadata = $class->getPropertyOrCreate($property->getName());

            $metadata->setType($types->getByStatement(
                statement: $this->getTypeStatement($property),
            ));

            if ($property->isReadOnly()) {
                $metadata->markAsReadonly();
            }

            if ($property->hasDefaultValue()) {
                $metadata->setDefaultValue($property->getDefaultValue());
            }
        }
    }

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

    private function createTypeStatement(\ReflectionType $type): TypeStatement
    {
        return match (true) {
            $type instanceof \ReflectionUnionType => $this->createUnionTypeStatement($type),
            $type instanceof \ReflectionIntersectionType => $this->createIntersectionTypeStatement($type),
            $type instanceof \ReflectionNamedType => $this->createNamedTypeStatement($type),
            default => throw new \LogicException(\sprintf(
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
