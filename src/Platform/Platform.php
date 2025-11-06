<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform;

use TypeLang\Mapper\Context\Direction;
use TypeLang\Mapper\Mapping\Provider\InMemoryProvider;
use TypeLang\Mapper\Mapping\Provider\MetadataBuilder;
use TypeLang\Mapper\Mapping\Provider\ProviderInterface;
use TypeLang\Mapper\Mapping\Reader\AttributeReader;
use TypeLang\Mapper\Mapping\Reader\ReaderInterface;
use TypeLang\Mapper\Mapping\Reader\ReflectionReader;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Type\TypeInterface;

use function TypeLang\Mapper\iterable_to_array;

abstract class Platform implements PlatformInterface
{
    protected readonly ProviderInterface $meta;

    /**
     * @var list<TypeBuilderInterface>
     */
    protected readonly array $types;

    /**
     * @var array<class-string<TypeInterface>, TypeCoercerInterface>
     */
    protected readonly array $coercers;

    /**
     * @param iterable<mixed, TypeBuilderInterface> $types
     * @param iterable<class-string<TypeInterface>, TypeCoercerInterface> $coercers
     */
    public function __construct(
        ProviderInterface|ReaderInterface|null $meta = null,
        iterable $types = [],
        iterable $coercers = [],
    ) {
        $this->meta = $this->formatMetadataProvider($meta);
        $this->types = $this->formatTypes($types);
        $this->coercers = $this->formatCoercers($coercers);
    }

    protected function formatMetadataProvider(ProviderInterface|ReaderInterface|null $meta): ProviderInterface
    {
        return match (true) {
            $meta instanceof ProviderInterface => $meta,
            $meta instanceof ReaderInterface => $this->createDefaultMetadataProvider($meta),
            default => $this->createDefaultMetadataProvider(),
        };
    }

    protected function createDefaultMetadataProvider(?ReaderInterface $reader = null): ProviderInterface
    {
        return new InMemoryProvider(
            delegate: new MetadataBuilder(
                reader: $reader ?? $this->createDefaultMetadataReader(),
            ),
        );
    }

    protected function createDefaultMetadataReader(): ReaderInterface
    {
        return new AttributeReader(
            delegate: new ReflectionReader(),
        );
    }

    /**
     * @param iterable<mixed, TypeBuilderInterface> $types
     *
     * @return list<TypeBuilderInterface>
     */
    protected function formatTypes(iterable $types): array
    {
        return iterable_to_array($types, false);
    }

    public function getTypes(Direction $direction): iterable
    {
        return $this->types;
    }

    /**
     * @param iterable<class-string<TypeInterface>, TypeCoercerInterface> $coercers
     *
     * @return array<class-string<TypeInterface>, TypeCoercerInterface>
     */
    protected function formatCoercers(iterable $coercers): array
    {
        return iterable_to_array($coercers);
    }

    public function getTypeCoercers(Direction $direction): iterable
    {
        return $this->coercers;
    }

    public function isFeatureSupported(GrammarFeature $feature): bool
    {
        return true;
    }
}
