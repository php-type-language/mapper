<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\ReflectionReader;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyInfo;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Mapper\Mapping\Metadata\TypeInfo;
use TypeLang\Parser\Node\Name;

final class TypePropertyReflectionLoader extends PropertyReflectionLoader
{
    public function load(PropertyInfo $info, \ReflectionProperty $property): void
    {
        $this->loadReadType($property, $info);
        $this->loadWriteType($property, $info);
    }

    private function loadReadType(\ReflectionProperty $property, PropertyInfo $info): void
    {
        $definition = $this->getReadTypeInfo($property);

        $info->read = $info->write = $definition;
    }

    private function loadWriteType(\ReflectionProperty $property, PropertyInfo $info): void
    {
        $definition = $this->findWriteTypeInfo($property);

        if ($definition === null) {
            return;
        }

        $info->write = $definition;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function findWriteTypeInfo(\ReflectionProperty $property): ?TypeInfo
    {
        if (\PHP_VERSION_ID < 80400) {
            return null;
        }

        // Force skip in case of setter is not defined
        if ($property->getHook(\PropertyHookType::Set) === null) {
            return null;
        }

        $definition = $this->createMixedTypeDefinition();
        $type = $property->getSettableType();

        if ($type !== null) {
            $definition = $this->createTypeDefinition($type);
        }

        return new TypeInfo(
            definition: $definition,
            source: $this->getWriteHookSourceInfo($property),
        );
    }

    private function getWriteHookSourceInfo(\ReflectionProperty $property): ?SourceInfo
    {
        if (\PHP_VERSION_ID < 80400) {
            return null;
        }

        return $this->getHookSourceInfo(
            hook: $property->getHook(\PropertyHookType::Set),
        );
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function getReadTypeInfo(\ReflectionProperty $property): TypeInfo
    {
        $definition = $this->createMixedTypeDefinition();
        $type = $property->getType();

        if ($type !== null) {
            $definition = $this->createTypeDefinition($type);
        }

        return new TypeInfo(
            definition: $definition,
            source: $this->getReadHookSourceInfo($property),
        );
    }

    private function getReadHookSourceInfo(\ReflectionProperty $property): ?SourceInfo
    {
        if (\PHP_VERSION_ID < 80400) {
            return null;
        }

        return $this->getHookSourceInfo(
            hook: $property->getHook(\PropertyHookType::Get),
        );
    }

    private function getHookSourceInfo(?\ReflectionMethod $hook): ?SourceInfo
    {
        if ($hook === null) {
            return null;
        }

        $file = $hook->getFileName();
        $line = $hook->getStartLine();

        if (\is_string($file) && $file !== '' && $line > 0) {
            return new SourceInfo($file, $line);
        }

        return null;
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
        $literal = $type->getName();

        // PHP 8.4 Setter's type bug
        if (\str_starts_with($literal, '?')) {
            $literal = \substr($literal, 1);
        }

        /** @phpstan-ignore-next-line : Type's name cannot be empty */
        $name = new Name($type->getName());

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
