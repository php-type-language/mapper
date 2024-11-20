<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bench;

use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Groups;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use TypeLang\Mapper\Bench\Stub\ExampleRequestDTO;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Mapping\Driver\AttributeDriver;
use TypeLang\Mapper\Mapping\Driver\Psr16CachedDriver;
use TypeLang\Mapper\Mapping\Driver\ReflectionDriver;
use TypeLang\Mapper\Platform\StandardPlatform;

#[Revs(10), Warmup(5), Iterations(10), BeforeMethods('prepare')]
final class TypeLangAttributesBench extends MapperBenchmark
{
    private readonly Mapper $raw;
    private readonly Mapper $cached;

    public function prepare(): void
    {
        parent::prepare();

        $driver = new AttributeDriver(
            delegate: new ReflectionDriver(),
        );

        $this->cached = new Mapper(
            platform: new StandardPlatform(
                driver: new Psr16CachedDriver(
                    cache: $this->psr16,
                    delegate: $driver,
                ),
            ),
        );

        $this->raw = new Mapper(
            platform: new StandardPlatform(
                driver: $driver,
            ),
        );
    }

    public function benchNormalization(): void
    {
        $this->raw->normalize($this->denormalized, ExampleRequestDTO::class);
    }

    public function benchCachedNormalization(): void
    {
        $this->cached->normalize($this->denormalized, ExampleRequestDTO::class);
    }

    public function benchDenormalization(): void
    {
        $this->raw->denormalize(self::NORMALIZED, ExampleRequestDTO::class);
    }

    public function benchCachedDenormalization(): void
    {
        $this->cached->denormalize(self::NORMALIZED, ExampleRequestDTO::class);
    }
}
