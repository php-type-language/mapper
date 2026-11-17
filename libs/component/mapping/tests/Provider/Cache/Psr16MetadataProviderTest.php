<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Provider\Cache;

use TypeLang\Mapper\Mapping\Provider\MetadataProviderInterface;
use TypeLang\Mapper\Mapping\Provider\Psr16MetadataProvider;
use TypeLang\Mapper\Mapping\Tests\Stub\ArrayCache;
use TypeLang\Mapper\Mapping\Tests\Stub\ArrayCachePool;

final class Psr16MetadataProviderTest extends CacheMetadataProviderTestCase
{
    protected function createCache(): ArrayCache
    {
        return new ArrayCache();
    }

    protected function createProvider(
        MetadataProviderInterface $delegate,
        ArrayCachePool|ArrayCache $cache,
        string $prefix = Psr16MetadataProvider::DEFAULT_PREFIX,
    ): MetadataProviderInterface {
        \assert($cache instanceof ArrayCache);

        return new Psr16MetadataProvider($delegate, $cache, $prefix);
    }
}
