<?php

declare(strict_types=1);

use TypeLang\Mapper\Mapper;

require __DIR__ . '/../../vendor/autoload.php';

class ExampleDTO
{
    /**
     * @param int<0, max> $value
     */
    public function __construct(
        public readonly int $value = 0,
    ) {}
}

$cache = new \Symfony\Component\Cache\Psr16Cache(
    pool: new \Symfony\Component\Cache\Adapter\FilesystemAdapter(
        namespace: 'typelang',
        directory: __DIR__ . '/../../var/cache',
    ),
);

$platform = new \TypeLang\Mapper\Platform\StandardPlatform(
    driver: new \TypeLang\Mapper\Mapping\Driver\Psr16CachedDriver(
        cache: $cache,
        delegate: new \TypeLang\Mapper\Mapping\Driver\DocBlockDriver(
            delegate: new \TypeLang\Mapper\Mapping\Driver\AttributeDriver(),
        ),
    ),
);

$mapper = new Mapper($platform);

var_dump($mapper->normalize(new ExampleDTO(42)));
//
// array:1 [
//   "value" => 42
// ]
//
