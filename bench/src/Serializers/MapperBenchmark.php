<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bench\Serializers;

use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TypeLang\Mapper\Bench\Stub\ExampleRequestDTO;
use TypeLang\Mapper\Mapper;

abstract class MapperBenchmark
{
    protected const NORMALIZED = [
        'name' => 'Example1',
        'items' => [
            [
                'name' => 'Example2',
                'items' => [
                    ['name' => 'Example3'],
                    ['name' => 'Example4'],
                    ['name' => 'Example5'],
                ],
            ],
            [
                'name' => 'Example6',
                'items' => [
                    ['name' => 'Example7'],
                    ['name' => 'Example8'],
                    ['name' => 'Example9'],
                ],
            ],
        ],
    ];

    protected const CACHE_DIR = __DIR__ . '/../../var';

    protected readonly ExampleRequestDTO $denormalized;

    protected function prepare(): void
    {
        $mapperFilename = new \ReflectionClass(Mapper::class)
            ->getFileName();
        $mapperDirectory = \dirname($mapperFilename);

        require_once $mapperDirectory . '/helpers.php';

        $this->denormalized = new ExampleRequestDTO(
            name: 'Example1',
            items: [
                new ExampleRequestDTO(
                    name: 'Example2',
                    items: [
                        new ExampleRequestDTO(name: 'Example3'),
                        new ExampleRequestDTO(name: 'Example4'),
                        new ExampleRequestDTO(name: 'Example5'),
                    ],
                ),
                new ExampleRequestDTO(
                    name: 'Example6',
                    items: [
                        new ExampleRequestDTO(name: 'Example7'),
                        new ExampleRequestDTO(name: 'Example8'),
                        new ExampleRequestDTO(name: 'Example9'),
                    ],
                ),
            ],
        );
    }

    protected function createPsr6Cache(string $namespace): CacheItemPoolInterface
    {
        return new FilesystemAdapter(
            namespace: $namespace,
            directory: self::CACHE_DIR,
        );
    }

    protected function createPsr16Cache(string $namespace): CacheInterface
    {
        return new Psr16Cache(
            pool: $this->createPsr6Cache($namespace),
        );
    }

    abstract public function benchNormalization(): void;

    abstract public function benchDenormalization(): void;
}
