<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\ReflectionReader;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyInfo;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Mapper\Mapping\Metadata\TypeInfo;
use TypeLang\Parser\Node\Name;

final class TypePropertyReflectionLoader extends PropertyReflectionLoader
{
    public function load(\ReflectionProperty $property, PropertyInfo $prototype): void
    {
        $this->loadReadType($property, $prototype);
        $this->loadWriteType($property, $prototype);
    }

    private function findSourceMap(\ReflectionProperty $property): ?SourceInfo
    {
        $class = $property->getDeclaringClass();

        if ($class->isInternal()) {
            return null;
        }

        $file = $class->getFileName();
        $line = $class->getStartLine();

        if ($file === false || $line < 1) {
            return null;
        }

        return new SourceInfo($file, $line);
    }

    private function loadReadType(\ReflectionProperty $property, PropertyInfo $info): void
    {
        $definition = $this->getReadTypeDefinition($property);

        $info->read = $info->write = new TypeInfo($definition, $this->findSourceMap($property));
    }

    private function loadWriteType(\ReflectionProperty $property, PropertyInfo $info): void
    {
        $definition = $this->findWriteTypeDefinition($property);

        if ($definition === null) {
            return;
        }

        $info->write = new TypeInfo($definition, $this->findSourceMap($property));
    }

    /**
     * @return non-empty-string
     * @throws \InvalidArgumentException
     */
    private function findWriteTypeDefinition(\ReflectionProperty $property): ?string
    {
        if (\PHP_VERSION_ID < 80400) {
            return null;
        }

        $type = $property->getSettableType();

        if ($type === null) {
            return $this->createMixedTypeDefinition();
        }

        return $this->createTypeDefinition($type);
    }

    /**
     * @return non-empty-string
     * @throws \InvalidArgumentException
     */
    private function getReadTypeDefinition(\ReflectionProperty $property): string
    {
        $type = $property->getType();

        if ($type === null) {
            return $this->createMixedTypeDefinition();
        }

        return $this->createTypeDefinition($type);
    }

    /**
     * @return non-empty-string
     */
    private function createMixedTypeDefinition(): string
    {
        return 'mixed';
    }

    /**
     * @return non-empty-string
     * @throws \InvalidArgumentException
     */
    private function createTypeDefinition(\ReflectionType $type): string
    {
        return match (true) {
            $type instanceof \ReflectionUnionType => $this->createUnionTypeDefinition($type),
            $type instanceof \ReflectionIntersectionType => $this->createIntersectionTypeDefinition($type),
            $type instanceof \ReflectionNamedType => $this->createNamedTypeDefinition($type),
            default => throw new \InvalidArgumentException(\sprintf(
                'Unsupported reflection type: %s',
                $type::class,
            )),
        };
    }

    /**
     * @return non-empty-string
     */
    private function createNamedTypeDefinition(\ReflectionNamedType $type): string
    {
        $result = $this->createNonNullNamedTypeDefinition($type);

        if ($type->allowsNull() && $type->getName() !== 'null') {
            return $result . '|null';
        }

        return $result;
    }

    /**
     * @return non-empty-string
     */
    private function createNonNullNamedTypeDefinition(\ReflectionNamedType $type): string
    {
        /** @phpstan-ignore-next-line : Type's name cannot be empty */
        $name = new Name($type->getName());
        $literal = $name->toString();

        if ($type->isBuiltin() || $name->isSpecial() || $name->isBuiltin()) {
            return $literal;
        }

        return \sprintf('\\%s', \ltrim($literal, '\\'));
    }

    /**
     * @return non-empty-string
     * @throws \InvalidArgumentException
     */
    private function createUnionTypeDefinition(\ReflectionUnionType $type): string
    {
        $children = [];

        foreach ($type->getTypes() as $child) {
            $children[] = $this->createTypeDefinition($child);
        }

        /** @var non-empty-list<non-empty-string> $children */
        return \implode('|', $children);
    }

    /**
     * @return non-empty-string
     * @throws \InvalidArgumentException
     */
    private function createIntersectionTypeDefinition(\ReflectionIntersectionType $type): string
    {
        $children = [];

        foreach ($type->getTypes() as $child) {
            $children[] = $this->createTypeDefinition($child);
        }

        /** @var non-empty-list<non-empty-string> $children */
        return '(' . \implode('&', $children) . ')';
    }
}
