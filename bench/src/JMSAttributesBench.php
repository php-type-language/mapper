<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bench;

use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Metadata\Cache\PsrCacheAdapter;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Groups;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use TypeLang\Mapper\Bench\Stub\ExampleRequestDTO;

#[Revs(10), Warmup(5), Iterations(10), BeforeMethods('prepare')]
final class JMSAttributesBench extends MapperBenchmark
{
    private readonly Serializer $raw;
    private readonly Serializer $cached;

    public function prepare(): void
    {
        parent::prepare();

        $this->raw = (new SerializerBuilder())
            ->enableEnumSupport()
            ->addDefaultListeners()
            ->addDefaultHandlers()
            ->addDefaultDeserializationVisitors()
            ->addDefaultSerializationVisitors()
            ->build();

        $this->cached = (new SerializerBuilder())
            ->enableEnumSupport()
            ->addDefaultListeners()
            ->addDefaultHandlers()
            ->addDefaultDeserializationVisitors()
            ->addDefaultSerializationVisitors()
            ->setCacheDir(__DIR__ . '/../var')
            ->setMetadataCache(new PsrCacheAdapter('jms', $this->psr6))
            ->build();
    }

    public function benchNormalization(): void
    {
        $this->raw->toArray($this->denormalized, type: ExampleRequestDTO::class);
    }

    public function benchCachedNormalization(): void
    {
        $this->cached->toArray($this->denormalized, type: ExampleRequestDTO::class);
    }

    public function benchDenormalization(): void
    {
        $this->raw->fromArray(self::NORMALIZED, ExampleRequestDTO::class);
    }

    public function benchCachedDenormalization(): void
    {
        $this->cached->fromArray(self::NORMALIZED, ExampleRequestDTO::class);
    }
}
