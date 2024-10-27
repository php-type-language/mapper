<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bench;

use CuyZ\Valinor\Normalizer\Normalizer;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface as SymfonyNormalizerInterface;
use TypeLang\Mapper\NormalizerInterface as TypeLangNormalizerInterface;
use TypeLang\Mapper\Bench\Stub\ExampleRequestDTO;

abstract class NormalizationBench implements BenchInterface
{
    protected ExampleRequestDTO $payload;

    protected TypeLangNormalizerInterface $typeLangDocBlock;

    protected TypeLangNormalizerInterface $typeLangAttributes;

    protected ArrayTransformerInterface $jms;

    protected Normalizer $valinor;

    protected SymfonyNormalizerInterface $symfonyPhpStan;

    protected SymfonyNormalizerInterface $symfonyDocBlock;

    public function benchJmsWithAttributes(): void
    {
        $this->jms->toArray($this->payload, type: ExampleRequestDTO::class);
    }

    public function benchValinorWithPhpStan(): void
    {
        $this->valinor->normalize($this->payload);
    }

    public function benchSymfonyWithPhpStan(): void
    {
        $this->symfonyPhpStan->normalize($this->payload);
    }

    public function benchSymfonyWithDocBlock(): void
    {
        $this->symfonyDocBlock->normalize($this->payload);
    }

    public function benchTypeLangWithDocBlocks(): void
    {
        $this->typeLangDocBlock->normalize($this->payload);
    }

    public function benchTypeLangWithAttributes(): void
    {
        $this->typeLangDocBlock->normalize($this->payload);
    }
}
