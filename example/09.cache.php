<?php

declare(strict_types=1);

use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Registry\Registry;

require __DIR__ . '/../vendor/autoload.php';

// The mapper allows you to cache information about objects (class metadata).
// To do this, you simply need to add the appropriate driver to the
// TypeLang\Mapper\Type\Builder\ObjectTypeBuilder, or specifying an argument
// to the TypeLang\Mapper\Platform\StandardPlatform which contains this
// object builder.

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
        directory: __DIR__ . '/var/cache',
    ),
);

$platform = new \TypeLang\Mapper\Platform\StandardPlatform(
    reader: new \TypeLang\Mapper\Mapping\Driver\Psr16CachedDriver(
        cache: $cache,
        delegate: new \TypeLang\Mapper\Mapping\Driver\DocBlockReader(
            delegate: new \TypeLang\Mapper\Mapping\Driver\AttributeReader(),
        ),
    ),
);

$mapper = new Mapper(new Registry($platform));

var_dump($mapper->normalize(new ExampleDTO(42)));
//
// array:1 [
//   "value" => 42
// ]
//
