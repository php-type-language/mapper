<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bench;

use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\Mapper\TreeMapper;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface as SymfonyDenormalizerInterface;
use TypeLang\Mapper\DenormalizerInterface as TypeLangDenormalizerInterface;
use TypeLang\Mapper\Bench\Stub\ExampleRequestDTO;

abstract class DenormalizationBench implements BenchInterface
{
    protected const PAYLOAD = [
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

    protected TypeLangDenormalizerInterface $typeLangDocBlock;

    protected TypeLangDenormalizerInterface $typeLangAttributes;

    protected ArrayTransformerInterface $jms;

    protected TreeMapper $valinor;

    protected SymfonyDenormalizerInterface $symfonyPhpStan;

    protected SymfonyDenormalizerInterface $symfonyDocBlock;

    public function benchJmsWithAttributes(): void
    {
        $this->jms->fromArray(self::PAYLOAD, ExampleRequestDTO::class);
    }

    public function benchValinorWithPhpStan(): void
    {
        $this->valinor->map(ExampleRequestDTO::class, Source::array(self::PAYLOAD));
    }

    public function benchSymfonyWithPhpStan(): void
    {
        $this->symfonyPhpStan->denormalize(self::PAYLOAD, ExampleRequestDTO::class);
    }

    public function benchSymfonyWithDocBlock(): void
    {
        $this->symfonyDocBlock->denormalize(self::PAYLOAD, ExampleRequestDTO::class);
    }

    public function benchTypeLangWithDocBlocks(): void
    {
        $this->typeLangDocBlock->denormalize(self::PAYLOAD, ExampleRequestDTO::class);
    }

    public function benchTypeLangWithAttributes(): void
    {
        $this->typeLangDocBlock->denormalize(self::PAYLOAD, ExampleRequestDTO::class);
    }
}
