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

#[Revs(100), Warmup(3), Iterations(5), BeforeMethods('prepare')]
final class SymfonyPHPStanWithSymfonyPsr6Bench extends MapperBenchmark
{
    private readonly Serializer $serializer;

    public function prepare(): void
    {
        parent::prepare();

        $this->serializer = (new Serializer([
            new ArrayDenormalizer(),
            new ObjectNormalizer(
                classMetadataFactory: new CacheClassMetadataFactory(
                    decorated: new ClassMetadataFactory(
                        loader: new AttributeLoader(),
                    ),
                    cacheItemPool: $this->createPsr6Cache('symfony-phpstan'),
                ),
                propertyTypeExtractor: new PhpStanExtractor(),
            ),
        ]));
    }

    public function benchNormalization(): void
    {
        $this->serializer->normalize($this->denormalized);
    }

    public function benchDenormalization(): void
    {
        $this->serializer->denormalize(self::NORMALIZED, ExampleRequestDTO::class);
    }
}
