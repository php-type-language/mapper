<?php

declare(strict_types=1);

use TypeLang\Mapper\Mapping\Reader\ReflectionReader as DefaultReader;
use TypeLang\Mapper\Mapper;

require __DIR__ . '/../../vendor/autoload.php';

$cache = new \Symfony\Component\Cache\Psr16Cache(
    pool: new \Symfony\Component\Cache\Adapter\FilesystemAdapter()
);

$platform = new \TypeLang\Mapper\Platform\StandardPlatform(
    // PSR-16 metadata provider: Stores metadata in PSR-16 cache
    meta: new \TypeLang\Mapper\Mapping\Provider\Psr16CacheProvider(
        psr16: $cache,
        // prefix: ...,
        // ttl: ...,
        delegate: new DefaultReader(),
    ),
);


$mapper = new Mapper($platform);
