<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver\ArrayConfigDriver;

use TypeLang\Mapper\Exception\Definition\PropertyTypeNotFoundException;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

final class TypePropertyConfigLoader extends PropertyConfigLoader
{
    public function load(
        array $config,
        \ReflectionProperty $property,
        PropertyMetadata $metadata,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): void {
        if (!\array_key_exists('type', $config)) {
            return;
        }

        // @phpstan-ignore-next-line : Additional DbC invariant
        assert(\is_string($config['type']));

        $metadata->type = $this->createPropertyType(
            type: $config['type'],
            property: $property,
            types: $types,
            parser: $parser,
        );
    }

    /**
     * @param non-empty-string $type
     *
     * @throws PropertyTypeNotFoundException in case of property type not found
     * @throws \Throwable in case of internal error occurs
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
