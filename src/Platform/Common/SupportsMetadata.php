<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Common;

use TypeLang\Mapper\Mapping\Provider\InMemoryProvider;
use TypeLang\Mapper\Mapping\Provider\MetadataBuilder;
use TypeLang\Mapper\Mapping\Provider\ProviderInterface;
use TypeLang\Mapper\Mapping\Reader\AttributeReader;
use TypeLang\Mapper\Mapping\Reader\ReaderInterface;
use TypeLang\Mapper\Mapping\Reader\ReflectionReader;

trait SupportsMetadata
{
    private readonly ProviderInterface $meta;

    final protected function getMetadataProvider(): ProviderInterface
    {
        /** @phpstan-ignore-next-line : Allow instantiation outside constructor */
        return $this->meta ??= $this->createDefaultMetadataProvider();
    }

    final protected function bootMetadataProviderIfNotBooted(ProviderInterface|ReaderInterface|null $meta): void
    {
        /** @phpstan-ignore-next-line : Allow instantiation outside constructor */
        $this->meta ??= match (true) {
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
}
