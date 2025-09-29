<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bench\Instantiator;

use Doctrine\Instantiator\Instantiator;
use Doctrine\Instantiator\InstantiatorInterface;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use TypeLang\Mapper\Bench\Stub\ExampleRequestDTO;

#[Revs(50), Warmup(5), Iterations(20), BeforeMethods('prepare')]
final class DoctrineInstantiatorBench extends InstantiatorBenchmark
{
    private readonly InstantiatorInterface $instantiator;

    public function prepare(): void
    {
        $this->instantiator = new Instantiator();
    }

    public function benchInstantiate(): void
    {
        $this->instantiator->instantiate(ExampleRequestDTO::class);
    }
}
