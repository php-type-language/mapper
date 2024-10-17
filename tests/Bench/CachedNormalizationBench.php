<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Bench;

use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Normalizer\Format;
use JMS\Serializer\SerializerBuilder;
use Metadata\Cache\PsrCacheAdapter;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\RetryThreshold;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpStanExtractor;
use Symfony\Component\Serializer\Mapping\Factory\CacheClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Mapping\Driver\AttributeDriver;
use TypeLang\Mapper\Mapping\Driver\DocBlockDriver;
use TypeLang\Mapper\Mapping\Driver\Psr16CachedDriver;
use TypeLang\Mapper\Mapping\Driver\ReflectionDriver;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Tests\Bench\Stub\ExampleRequestDTO;

#[Revs(50), Warmup(5), Iterations(30), RetryThreshold(5), BeforeMethods('prepare')]
final class CachedNormalizationBench extends NormalizationBench
{
    public function prepare(): void
    {
        $psr6 = new FilesystemAdapter(
            namespace: 'benchmarks',
            directory: __DIR__ . '/../var',
        );

        $psr16 = new Psr16Cache(
            pool: $psr6,
        );

        $this->payload = new ExampleRequestDTO(
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

        $this->typeLangDocBlock = new Mapper(
            platform: new StandardPlatform(
                driver: new Psr16CachedDriver(
                    cache: $psr16,
                    delegate: new DocBlockDriver(
                        delegate: new ReflectionDriver(),
                    ),
                ),
            ),
        );

        $this->typeLangAttributes = new Mapper(
            platform: new StandardPlatform(
                driver: new Psr16CachedDriver(
                    cache: $psr16,
                    delegate: new AttributeDriver(
                        delegate: new ReflectionDriver(),
                    ),
                ),
            ),
        );

        $this->jms = (new SerializerBuilder())
            ->setCacheDir(__DIR__ . '/../var')
            ->setMetadataCache(new PsrCacheAdapter('jms', $psr6))
            ->build();

        $this->valinor = (new MapperBuilder())
            ->withCache($psr16)
            ->normalizer(Format::array());

        $this->symfonyPhpStan = (new Serializer([
            new ArrayDenormalizer(),
            new ObjectNormalizer(
                classMetadataFactory: new CacheClassMetadataFactory(
                    decorated: new ClassMetadataFactory(
                        loader: new AttributeLoader(),
                    ),
                    cacheItemPool: $psr6,
                ),
                propertyTypeExtractor: new PhpStanExtractor(),
            ),
        ]));

        $this->symfonyDocBlock = (new Serializer([
            new ArrayDenormalizer(),
            new ObjectNormalizer(
                classMetadataFactory: new CacheClassMetadataFactory(
                    decorated: new ClassMetadataFactory(
                        loader: new AttributeLoader(),
                    ),
                    cacheItemPool: $psr6,
                ),
                propertyTypeExtractor: new PhpDocExtractor()
            ),
        ]));
    }
}
