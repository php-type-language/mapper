<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bench\Serializers;

use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TypeLang\Mapper\Bench\Stub\ExampleRequestDTO;

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

    protected readonly ExampleRequestDTO $denormalized;

    protected readonly CacheItemPoolInterface $psr6;

    protected readonly CacheInterface $psr16;

    protected function prepare(): void
    {
        $this->psr6 = new FilesystemAdapter(
            namespace: 'benchmarks',
            directory: __DIR__ . '/../../var',
        );

        $this->psr16 = new Psr16Cache(
            pool: $this->psr6,
        );

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

    abstract public function benchNormalization(): void;
    abstract public function benchCachedNormalization(): void;
    abstract public function benchDenormalization(): void;
    abstract public function benchCachedDenormalization(): void;
}
