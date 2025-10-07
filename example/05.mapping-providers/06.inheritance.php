<?php

declare(strict_types=1);

use TypeLang\Mapper\Mapping\Reader\ReflectionReader as DefaultReader;
use TypeLang\Mapper\Mapper;

require __DIR__ . '/../../vendor/autoload.php';

$cacheItemPool = new \Symfony\Component\Cache\Adapter\FilesystemAdapter();

$platform = new \TypeLang\Mapper\Platform\StandardPlatform(
    // RAM PROVIDER
    meta: new \TypeLang\Mapper\Mapping\Provider\InMemoryProvider(
    //  PSR-6 PROVIDER
        delegate: new \TypeLang\Mapper\Mapping\Provider\Psr6CacheProvider(
            psr6: $cacheItemPool,
    //      ATTRIBUTE READER
            delegate: new \TypeLang\Mapper\Mapping\Reader\AttributeReader(
    //          PHPDOC READER
                delegate: new \TypeLang\Mapper\Mapping\Reader\PhpDocReader(
    //              REFLECTION READER
                    delegate: new \TypeLang\Mapper\Mapping\Reader\ReflectionReader(),
                ),
            ),
        ),
    )
);


$mapper = new Mapper($platform);
