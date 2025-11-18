<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bench\Instantiator;

use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use TypeLang\Mapper\Bench\Stub\ExampleRequestDTO;
use TypeLang\Mapper\Kernel\Instantiator\ReflectionClassInstantiator;

#[Revs(50), Warmup(5), Iterations(20), BeforeMethods('prepare')]
final class ReflectionInstantiatorBench extends InstantiatorBenchmark
{
    private readonly ReflectionClassInstantiator $instantiator;

    public function prepare(): void
    {
        $this->instantiator = new ReflectionClassInstantiator();
    }

    public function benchInstantiate(): void
    {
        $this->instantiator->instantiate(ExampleRequestDTO::class);
    }
}
