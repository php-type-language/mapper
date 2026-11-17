<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Provider\Cache;

use TypeLang\Mapper\Mapping\Provider\MetadataProviderInterface;
use TypeLang\Mapper\Mapping\Provider\Psr6MetadataProvider;
use TypeLang\Mapper\Mapping\Tests\Stub\ArrayCache;
use TypeLang\Mapper\Mapping\Tests\Stub\ArrayCachePool;

final class Psr6MetadataProviderTest extends CacheMetadataProviderTestCase
{
    protected function createCache(): ArrayCachePool
    {
        return new ArrayCachePool();
    }

    protected function createProvider(
        MetadataProviderInterface $delegate,
        ArrayCachePool|ArrayCache $cache,
        string $prefix = Psr6MetadataProvider::DEFAULT_PREFIX,
    ): MetadataProviderInterface {
        \assert($cache instanceof ArrayCachePool);

        return new Psr6MetadataProvider($delegate, $cache, $prefix);
    }
}
