<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bench\Serializers;

use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use TypeLang\Mapper\Bench\Stub\ExampleRequestDTO;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Mapping\Provider\AttributeDriver;
use TypeLang\Mapper\Mapping\Provider\Psr16CachedDriver;
use TypeLang\Mapper\Mapping\Provider\ReflectionDriver;
use TypeLang\Mapper\Platform\StandardPlatform;

#[Revs(30), Warmup(3), Iterations(5), BeforeMethods('prepare')]
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
                meta: new Psr16CachedDriver(
                    cache: $this->psr16,
                    delegate: $driver,
                ),
            ),
        );

        $this->raw = new Mapper(
            platform: new StandardPlatform(
                meta: $driver,
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
