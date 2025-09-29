<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bench\Serializers;

use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Symfony\Component\PropertyInfo\Extractor\PhpStanExtractor;
use Symfony\Component\Serializer\Mapping\Factory\CacheClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use TypeLang\Mapper\Bench\Stub\ExampleRequestDTO;

#[Revs(30), Warmup(3), Iterations(5), BeforeMethods('prepare')]
final class SymfonyPHPStanBench extends MapperBenchmark
{
    private readonly Serializer $raw;
    private readonly Serializer $cached;

    public function prepare(): void
    {
        parent::prepare();

        $this->cached = (new Serializer([
            new ArrayDenormalizer(),
            new ObjectNormalizer(
                classMetadataFactory: new CacheClassMetadataFactory(
                    decorated: new ClassMetadataFactory(
                        loader: new AttributeLoader(),
                    ),
                    cacheItemPool: $this->psr6,
                ),
                propertyTypeExtractor: new PhpStanExtractor(),
            ),
        ]));

        $this->raw = (new Serializer([
            new ArrayDenormalizer(),
            new ObjectNormalizer(propertyTypeExtractor: new PhpStanExtractor()),
        ]));
    }

    public function benchNormalization(): void
    {
        $this->raw->normalize($this->denormalized);
    }

    public function benchCachedNormalization(): void
    {
        $this->cached->normalize($this->denormalized);
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
