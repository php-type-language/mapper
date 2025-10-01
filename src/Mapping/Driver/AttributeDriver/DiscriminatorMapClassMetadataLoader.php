<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver\AttributeDriver;

use TypeLang\Mapper\Exception\Definition\PropertyTypeNotFoundException;
use TypeLang\Mapper\Mapping\DiscriminatorMap;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\DiscriminatorMapMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

final class DiscriminatorMapClassMetadataLoader extends ClassMetadataLoader
{
    public function load(
        \ReflectionClass $class,
        ClassMetadata $metadata,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): void {
        $attribute = $this->findClassAttribute($class, DiscriminatorMap::class);

        if ($attribute === null) {
            return;
        }

        $mapping = [];
        $default = null;

        foreach ($attribute->map as $mappedValue => $mappedType) {
            $mapping[$mappedValue] = $this->createDiscriminatorType(
                type: $mappedType,
                class: $class,
                types: $types,
                parser: $parser,
            );
        }

        if ($attribute->otherwise !== null) {
            $default = $this->createDiscriminatorType(
                type: $attribute->otherwise,
                class: $class,
                types: $types,
                parser: $parser,
            );
        }

        $metadata->discriminator = new DiscriminatorMapMetadata(
            field: $attribute->field,
            map: $mapping,
            default: $default,
        );
    }

    /**
     * @param non-empty-string $type
     * @param \ReflectionClass<object> $class
     *
     * @throws PropertyTypeNotFoundException
     * @throws \Throwable
     */
    private function createDiscriminatorType(
        string $type,
        \ReflectionClass $class,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): TypeMetadata {
        $statement = $parser->getStatementByDefinition($type);

        // TODO Add custom "discriminator type exception"
        $instance = $types->getTypeByStatement($statement, $class);

        return new TypeMetadata($instance, $statement);
    }
}
