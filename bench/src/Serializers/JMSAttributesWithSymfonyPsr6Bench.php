<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bench\Serializers;

use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Metadata\Cache\PsrCacheAdapter;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use TypeLang\Mapper\Bench\Stub\ExampleRequestDTO;

#[Revs(100), Warmup(3), Iterations(5), BeforeMethods('prepare')]
final class JMSAttributesWithSymfonyPsr6Bench extends MapperBenchmark
{
    private readonly Serializer $serializer;

    public function prepare(): void
    {
        parent::prepare();

        $this->serializer = (new SerializerBuilder())
            ->enableEnumSupport()
            ->addDefaultListeners()
            ->addDefaultHandlers()
            ->addDefaultDeserializationVisitors()
            ->addDefaultSerializationVisitors()
            ->setCacheDir(self::CACHE_DIR . '/jms')
            ->setMetadataCache(new PsrCacheAdapter('jms', $this->createPsr6Cache('jms')))
            ->build();
    }

    public function benchNormalization(): void
    {
        $this->serializer->toArray($this->denormalized, type: ExampleRequestDTO::class);
    }

    public function benchDenormalization(): void
    {
        $this->serializer->fromArray(self::NORMALIZED, ExampleRequestDTO::class);
    }
}
