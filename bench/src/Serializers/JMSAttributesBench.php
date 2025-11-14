<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bench\Serializers;

use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use TypeLang\Mapper\Bench\Stub\ExampleRequestDTO;

#[Revs(100), Warmup(3), Iterations(5), BeforeMethods('prepare')]
final class JMSAttributesBench extends MapperBenchmark
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
            ->build();
    }

    public function benchNormalization(): void
    {
        $result = $this->serializer->toArray($this->denormalized, type: ExampleRequestDTO::class);

        assert($this->isNormalized($result));
    }

    public function benchDenormalization(): void
    {
        $result = $this->serializer->fromArray(self::NORMALIZED, ExampleRequestDTO::class);

        assert($this->isDenormalized($result));
    }
}
