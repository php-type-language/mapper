<?php

declare(strict_types=1);

use TypeLang\Mapper\Mapping\Reader\ReflectionReader as DefaultReader;
use TypeLang\Mapper\Mapper;

require __DIR__ . '/../../vendor/autoload.php';

$cacheItemPool = new \Symfony\Component\Cache\Adapter\FilesystemAdapter();

$platform = new \TypeLang\Mapper\Platform\StandardPlatform(
    // First, let's work with RAM
    meta: new \TypeLang\Mapper\Mapping\Provider\InMemoryProvider(
        // If the data is missing, we turn to the PSR-6 file cache
        delegate: new \TypeLang\Mapper\Mapping\Provider\Psr6CacheProvider(
            psr6: $cacheItemPool,
            delegate: new DefaultReader(),
        ),
    )
);


$mapper = new Mapper($platform);
