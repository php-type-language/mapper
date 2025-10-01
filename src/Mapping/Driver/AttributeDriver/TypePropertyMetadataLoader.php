<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver\AttributeDriver;

use TypeLang\Mapper\Exception\Definition\PropertyTypeNotFoundException;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

final class TypePropertyMetadataLoader extends PropertyMetadataLoader
{
    /**
     * @throws \Throwable
     */
    public function load(
        \ReflectionProperty $property,
        PropertyMetadata $metadata,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): void {
        $this->loadPropertyType($property, $metadata, $types, $parser);

        if (\PHP_VERSION_ID < 80400) {
            return;
        }

        $this->loadReadHookType($property, $metadata, $types, $parser);
        $this->loadWriteHookType($property, $metadata, $types, $parser);
    }

    /**
     * @throws \Throwable
     */
    private function loadPropertyType(
        \ReflectionProperty $property,
        PropertyMetadata $metadata,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): void {
        $attribute = $this->findPropertyAttribute($property, MapType::class);

        if ($attribute === null) {
            return;
        }

        $metadata->read = $metadata->write = $this->createPropertyType(
            type: $attribute->type,
            property: $property,
            types: $types,
            parser: $parser,
        );
    }

    /**
     * @throws \Throwable
     */
    private function loadReadHookType(
        \ReflectionProperty $property,
        PropertyMetadata $metadata,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): void {
        $hook = $property->getHook(\PropertyHookType::Get);

        if ($hook === null) {
            return;
        }

        $attribute = $this->findHookAttribute($hook, MapType::class);

        if ($attribute === null) {
            return;
        }

        $metadata->read = $this->createPropertyType(
            type: $attribute->type,
            property: $property,
            types: $types,
            parser: $parser,
        );
    }

    /**
     * @throws \Throwable
     */
    private function loadWriteHookType(
        \ReflectionProperty $property,
        PropertyMetadata $metadata,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): void {
        $hook = $property->getHook(\PropertyHookType::Set);

        if ($hook === null) {
            return;
        }

        $attribute = $this->findHookAttribute($hook, MapType::class);

        if ($attribute === null) {
            return;
        }

        $metadata->write = $this->createPropertyType(
            type: $attribute->type,
            property: $property,
            types: $types,
            parser: $parser,
        );
    }

    /**
     * @param non-empty-string $type
     *
     * @throws PropertyTypeNotFoundException
     * @throws \Throwable
     */
    private function createPropertyType(
        string $type,
        \ReflectionProperty $property,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): TypeMetadata {
        $statement = $parser->getStatementByDefinition($type);

        $class = $property->getDeclaringClass();

        try {
            $instance = $types->getTypeByStatement($statement, $class);
        } catch (TypeNotFoundException $e) {
            throw PropertyTypeNotFoundException::becauseTypeOfPropertyNotDefined(
                class: $class->name,
                property: $property->name,
                type: $e->getType(),
                previous: $e,
            );
        }

        return new TypeMetadata($instance, $statement);
    }
}
