<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bench\Serializers;

use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\Normalizer\Normalizer;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use TypeLang\Mapper\Bench\Stub\ExampleRequestDTO;

#[Revs(30), Warmup(3), Iterations(5), BeforeMethods('prepare')]
final class ValinorBench extends MapperBenchmark
{
    private readonly TreeMapper $rawMapper;
    private readonly TreeMapper $cachedMapper;
    private readonly Normalizer $rawNormalizer;
    private readonly Normalizer $cachedNormalizer;

    public function prepare(): void
    {
        parent::prepare();

        $this->rawMapper = (new MapperBuilder())
            ->mapper();

        $this->cachedMapper = (new MapperBuilder())
            ->withCache($this->psr16)
            ->mapper();

        $this->rawNormalizer = (new MapperBuilder())
            ->normalizer(Format::array());

        $this->cachedNormalizer = (new MapperBuilder())
            ->withCache($this->psr16)
            ->normalizer(Format::array());
    }

    public function benchNormalization(): void
    {
        $this->rawNormalizer->normalize($this->denormalized);
    }

    public function benchCachedNormalization(): void
    {
        $this->cachedNormalizer->normalize($this->denormalized);
    }

    public function benchDenormalization(): void
    {
        $this->rawMapper->map(ExampleRequestDTO::class, Source::array(self::NORMALIZED));
    }

    public function benchCachedDenormalization(): void
    {
        $this->cachedMapper->map(ExampleRequestDTO::class, Source::array(self::NORMALIZED));
    }
}
