<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Bench;

use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializerBuilder;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpStanExtractor;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface as SymfonyDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use TypeLang\Mapper\DenormalizerInterface as TypeLangDenormalizerInterface;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Mapping\Driver\AttributeDriver;
use TypeLang\Mapper\Mapping\Driver\DocBlockDriver;
use TypeLang\Mapper\Mapping\Driver\ReflectionDriver;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Tests\Bench\Stub\ExampleRequestDTO;

#[Revs(100), Warmup(5), Iterations(10)]
#[BeforeMethods('prepare')]
final class DenormalizationBench implements BenchInterface
{
    private const PAYLOAD = [
        'name' => 'Example1',
        'items' => [
            [
                'name' => 'Example2',
                'items' => [
                    ['name' => 'Example3'],
                    ['name' => 'Example4'],
                    ['name' => 'Example5'],
                ],
            ],
            [
                'name' => 'Example6',
                'items' => [
                    ['name' => 'Example7'],
                    ['name' => 'Example8'],
                    ['name' => 'Example9'],
                ],
            ],
        ],
    ];

    private readonly TypeLangDenormalizerInterface $typeLangDocBlock;

    private readonly TypeLangDenormalizerInterface $typeLangAttributes;

    private readonly ArrayTransformerInterface $jms;

    private readonly TreeMapper $valinor;

    private readonly SymfonyDenormalizerInterface $symfonyPhpStan;

    private readonly SymfonyDenormalizerInterface $symfonyDocBlock;

    public function prepare(): void
    {
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

    public function benchJms(): void
    {
        $this->jms->fromArray(self::PAYLOAD, ExampleRequestDTO::class);
    }

    public function benchValinor(): void
    {
        $this->valinor->map(ExampleRequestDTO::class, Source::array(self::PAYLOAD));
    }

    public function benchSymfonyPhpStan(): void
    {
        $this->symfonyPhpStan->denormalize(self::PAYLOAD, ExampleRequestDTO::class);
    }

    public function benchSymfonyDocBlock(): void
    {
        $this->symfonyDocBlock->denormalize(self::PAYLOAD, ExampleRequestDTO::class);
    }

    public function benchTypeLangDocBlock(): void
    {
        $this->typeLangDocBlock->denormalize(self::PAYLOAD, ExampleRequestDTO::class);
    }

    public function benchTypeLangAttributes(): void
    {
        $this->typeLangDocBlock->denormalize(self::PAYLOAD, ExampleRequestDTO::class);
    }
}
