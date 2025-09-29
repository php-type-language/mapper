<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bench\Instantiator;

abstract class InstantiatorBenchmark
{
    abstract public function benchInstantiate(): void;
}
