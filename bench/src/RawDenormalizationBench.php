<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bench;

use CuyZ\Valinor\MapperBuilder;
use JMS\Serializer\SerializerBuilder;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\RetryThreshold;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpStanExtractor;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Mapping\Driver\AttributeDriver;
use TypeLang\Mapper\Mapping\Driver\DocBlockDriver;
use TypeLang\Mapper\Mapping\Driver\ReflectionDriver;
use TypeLang\Mapper\Platform\StandardPlatform;

#[Revs(50), Warmup(5), Iterations(30), RetryThreshold(5), BeforeMethods('prepare')]
final class RawDenormalizationBench extends DenormalizationBench
{
    public function prepare(): void
    {
        $this->typeLangDocBlock = new Mapper(
            platform: new StandardPlatform(
                driver: new DocBlockDriver(
                    delegate: new ReflectionDriver(),
                ),
            ),
        );

        $this->typeLangAttributes = new Mapper(
            platform: new StandardPlatform(
                driver: new AttributeDriver(
                    delegate: new ReflectionDriver(),
                ),
            ),
        );

        $this->jms = (new SerializerBuilder())
            ->build();

        $this->valinor = (new MapperBuilder())
            ->mapper();

        $this->symfonyPhpStan = (new Serializer([
            new ArrayDenormalizer(),
            new ObjectNormalizer(propertyTypeExtractor: new PhpStanExtractor()),
        ]));

        $this->symfonyDocBlock = (new Serializer([
            new ArrayDenormalizer(),
            new ObjectNormalizer(propertyTypeExtractor: new PhpDocExtractor()),
        ]));
    }
}
