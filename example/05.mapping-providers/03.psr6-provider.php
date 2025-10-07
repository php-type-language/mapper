<?php

declare(strict_types=1);

use TypeLang\Mapper\Mapping\Reader\ReflectionReader as DefaultReader;
use TypeLang\Mapper\Mapper;

require __DIR__ . '/../../vendor/autoload.php';

$cacheItemPool = new \Symfony\Component\Cache\Adapter\FilesystemAdapter();

$platform = new \TypeLang\Mapper\Platform\StandardPlatform(
    // PSR-6 metadata provider: Stores metadata in PSR-6 cache pool
    meta: new \TypeLang\Mapper\Mapping\Provider\Psr6CacheProvider(
        psr6: $cacheItemPool,
        // prefix: ...,
        // ttl: ...,
        delegate: new DefaultReader(),
    ),
);


$mapper = new Mapper($platform);
