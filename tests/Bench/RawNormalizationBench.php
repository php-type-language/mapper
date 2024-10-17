<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Bench;

use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Normalizer\Format;
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
use TypeLang\Mapper\Tests\Bench\Stub\ExampleRequestDTO;

#[Revs(50), Warmup(5), Iterations(10), RetryThreshold(5), BeforeMethods('prepare')]
final class RawNormalizationBench extends NormalizationBench
{
    public function prepare(): void
    {
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
                driver: new DocBlockDriver(
                    delegate: new ReflectionDriver()
                )
            ),
        );

        $this->typeLangAttributes = new Mapper(
            platform: new StandardPlatform(
                driver: new AttributeDriver(
                    delegate: new ReflectionDriver()
                )
            ),
        );

        $this->jms = (new SerializerBuilder())
            ->enableEnumSupport()
            ->build();

        $this->valinor = (new MapperBuilder())
            ->normalizer(Format::array());

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
