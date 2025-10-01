<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver\AttributeDriver;

use TypeLang\Mapper\Exception\Definition\PropertyTypeNotFoundException;
use TypeLang\Mapper\Mapping\Driver\ArrayConfigDriver\ClassConfigLoader;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\DiscriminatorMapMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

final class DiscriminatorMapClassConfigLoader extends ClassConfigLoader
{
    public function load(
        array $config,
        \ReflectionClass $class,
        ClassMetadata $metadata,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): void {
        if (!\array_key_exists('discriminator', $config)) {
            return;
        }

        // @phpstan-ignore-next-line : Additional DbC invariant
        assert(\is_array($config['discriminator']));

        $discriminatorConfig = $config['discriminator'];

        // @phpstan-ignore-next-line : Additional DbC invariant
        assert(\array_key_exists('field', $discriminatorConfig));

        $discriminator = new DiscriminatorMapMetadata(
            field: $discriminatorConfig['field'],
        );

        // @phpstan-ignore-next-line : Additional DbC invariant
        assert(\array_key_exists('map', $discriminatorConfig));

        // @phpstan-ignore-next-line : Additional DbC invariant
        assert(\is_array($discriminatorConfig['map']));

        foreach ($discriminatorConfig['map'] as $discriminatorValue => $discriminatorType) {
            // @phpstan-ignore-next-line : Additional DbC invariant
            assert(\is_string($discriminatorValue));

            // @phpstan-ignore-next-line : Additional DbC invariant
            assert(\is_string($discriminatorType));

            $discriminator->addType(
                fieldValue: $discriminatorValue,
                type: $this->createDiscriminatorType(
                    type: $discriminatorType,
                    class: $class,
                    types: $types,
                    parser: $parser,
                ),
            );
        }

        // @phpstan-ignore-next-line : Additional DbC invariant
        assert(\array_key_exists('otherwise', $discriminatorConfig));

        // @phpstan-ignore-next-line : Additional DbC invariant
        assert(\is_string($discriminatorConfig['otherwise']));

        $discriminator->default = $this->createDiscriminatorType(
            type: $discriminatorConfig['otherwise'],
            class: $class,
            types: $types,
            parser: $parser,
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
