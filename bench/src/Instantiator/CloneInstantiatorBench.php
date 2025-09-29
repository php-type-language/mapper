<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bench\Instantiator;

use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use TypeLang\Mapper\Bench\Stub\ExampleRequestDTO;
use TypeLang\Mapper\Runtime\ClassInstantiator\CloneClassInstantiator;
use TypeLang\Mapper\Runtime\ClassInstantiator\ReflectionClassInstantiator;

#[Revs(50), Warmup(5), Iterations(20), BeforeMethods('prepare')]
final class CloneInstantiatorBench extends InstantiatorBenchmark
{
    private readonly CloneClassInstantiator $instantiator;

    public function prepare(): void
    {
        $this->instantiator = new CloneClassInstantiator(
            delegate: new ReflectionClassInstantiator(),
        );
    }

    public function benchInstantiate(): void
    {
        $this->instantiator->instantiate(ExampleRequestDTO::class);
    }
}
