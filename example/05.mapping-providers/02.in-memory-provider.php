<?php

declare(strict_types=1);

use TypeLang\Mapper\Mapping\Reader\ReflectionReader as DefaultReader;
use TypeLang\Mapper\Mapper;

require __DIR__ . '/../../vendor/autoload.php';

$platform = new \TypeLang\Mapper\Platform\StandardPlatform(
    // InMemory metadata provider: Stores metadata in RAM
    meta: new \TypeLang\Mapper\Mapping\Provider\InMemoryProvider(
        delegate: new DefaultReader(),
    ),
);


$mapper = new Mapper($platform);
