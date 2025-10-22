<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform;

use TypeLang\Mapper\Mapping\Provider\InMemoryProvider;
use TypeLang\Mapper\Mapping\Provider\MetadataReaderProvider;
use TypeLang\Mapper\Mapping\Provider\ProviderInterface;
use TypeLang\Mapper\Mapping\Reader\AttributeReader;
use TypeLang\Mapper\Mapping\Reader\ReaderInterface;
use TypeLang\Mapper\Mapping\Reader\ReflectionReader;
use TypeLang\Mapper\Runtime\Context\Direction;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;

abstract class Platform implements PlatformInterface
{
    protected readonly ProviderInterface $meta;

    /**
     * @var list<TypeBuilderInterface>
     */
    protected readonly array $types;

    /**
     * @param iterable<mixed, TypeBuilderInterface> $types
     */
    public function __construct(
        ProviderInterface|ReaderInterface|null $meta = null,
        iterable $types = [],
    ) {
        $this->meta = match (true) {
            $meta instanceof ProviderInterface => $meta,
            $meta instanceof ReaderInterface => $this->createDefaultMetadataProvider($meta),
            default => $this->createDefaultMetadataProvider(),
        };

        $this->types = match (true) {
            $types instanceof \Traversable => \iterator_to_array($types, false),
            \array_is_list($types) => $types,
            default => \array_values($types),
        };
    }

    public function getTypes(Direction $direction): iterable
    {
        return $this->types;
    }

    protected function createDefaultMetadataProvider(?ReaderInterface $reader = null): ProviderInterface
    {
        return new InMemoryProvider(
            delegate: new MetadataReaderProvider(
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
}
