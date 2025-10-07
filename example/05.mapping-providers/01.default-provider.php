<?php

declare(strict_types=1);

// Reflection metadata reader (see 04.mapping-readers/01.reflection-mapping.php)
use TypeLang\Mapper\Mapping\Reader\ReflectionReader as DefaultReader;
use TypeLang\Mapper\Mapper;

require __DIR__ . '/../../vendor/autoload.php';


$platform = new \TypeLang\Mapper\Platform\StandardPlatform(
    // Default metadata provider
    meta: new \TypeLang\Mapper\Mapping\Provider\MetadataReaderProvider(
        reader: new DefaultReader(),
    ),
);


$mapper = new Mapper($platform);
