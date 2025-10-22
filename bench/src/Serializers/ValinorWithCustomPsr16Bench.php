<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bench\Serializers;

use CuyZ\Valinor\Cache\FileSystemCache;
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

#[Revs(100), Warmup(3), Iterations(5), BeforeMethods('prepare')]
final class ValinorWithCustomPsr16Bench extends MapperBenchmark
{
    private readonly TreeMapper $mapper;
    private readonly Normalizer $normalizer;

    public function prepare(): void
    {
        parent::prepare();

        $builder = (new MapperBuilder())
            ->withCache(new FileSystemCache(
                cacheDir: self::CACHE_DIR . '/valinor',
            ));

        $this->mapper = $builder->mapper();
        $this->normalizer = $builder->normalizer(Format::array());
    }

    public function benchNormalization(): void
    {
        $this->normalizer->normalize($this->denormalized);
    }

    public function benchDenormalization(): void
    {
        $this->mapper->map(ExampleRequestDTO::class, Source::array(self::NORMALIZED));
    }
}
