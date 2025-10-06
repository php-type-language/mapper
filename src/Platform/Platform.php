<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform;

use TypeLang\Mapper\Mapping\Provider\MetadataReaderProvider;
use TypeLang\Mapper\Mapping\Provider\ProviderInterface;
use TypeLang\Mapper\Mapping\Provider\InMemoryProvider;
use TypeLang\Mapper\Mapping\Reader\AttributeReader;
use TypeLang\Mapper\Mapping\Reader\ReaderInterface;
use TypeLang\Mapper\Mapping\Reader\ReflectionReader;

abstract class Platform implements PlatformInterface
{
    protected readonly ProviderInterface $meta;

    public function __construct(ProviderInterface|ReaderInterface|null $meta = null)
    {
        $this->meta = match (true) {
            $meta instanceof ProviderInterface => $meta,
            $meta instanceof ReaderInterface => $this->createDefaultMetadataProvider($meta),
            default => $this->createDefaultMetadataProvider(),
        };
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
