<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Bench;

use CuyZ\Valinor\MapperBuilder;
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

#[Revs(50), Warmup(5), Iterations(10), RetryThreshold(5), BeforeMethods('prepare')]
final class CachedDenormalizationBench extends DenormalizationBench
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
            ->enableEnumSupport()
            ->build();

        $this->valinor = (new MapperBuilder())
            ->withCache($psr16)
            ->mapper();

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
