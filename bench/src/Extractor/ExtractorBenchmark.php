<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bench\Extractor;

abstract class ExtractorBenchmark
{
    abstract public function benchExtract(): void;
}
