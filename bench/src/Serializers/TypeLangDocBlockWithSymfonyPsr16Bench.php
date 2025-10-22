<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bench\Serializers;

use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use TypeLang\Mapper\Bench\Stub\ExampleRequestDTO;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Mapping\Provider\Psr16CacheProvider;
use TypeLang\Mapper\Mapping\Reader\PhpDocReader;
use TypeLang\Mapper\Platform\StandardPlatform;

#[Revs(100), Warmup(3), Iterations(5), BeforeMethods('prepare')]
final class TypeLangDocBlockWithSymfonyPsr16Bench extends MapperBenchmark
{
    private readonly Mapper $mapper;

    public function prepare(): void
    {
        parent::prepare();

        $this->mapper = new Mapper(
            platform: new StandardPlatform(
                meta: new Psr16CacheProvider(
                    psr16: $this->createPsr16Cache('tl-doc-psr16'),
                    delegate: new PhpDocReader(),
                ),
            ),
        );
    }

    public function benchNormalization(): void
    {
        $this->mapper->normalize($this->denormalized, ExampleRequestDTO::class);
    }

    public function benchDenormalization(): void
    {
        $this->mapper->denormalize(self::NORMALIZED, ExampleRequestDTO::class);
    }
}
