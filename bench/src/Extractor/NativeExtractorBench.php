<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bench\Extractor;

use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use TypeLang\Mapper\Type\Extractor\NativeTypeExtractor;

#[Revs(50), Warmup(5), Iterations(20), BeforeMethods('prepare')]
final class NativeExtractorBench extends ExtractorBenchmark
{
    private readonly NativeTypeExtractor $extractor;

    public function prepare(): void
    {
        $this->extractor = new NativeTypeExtractor();
    }

    public function benchExtract(): void
    {
        $this->extractor->getDefinitionByValue(null);
        $this->extractor->getDefinitionByValue(true);
        $this->extractor->getDefinitionByValue(false);
        $this->extractor->getDefinitionByValue(\NAN);
        $this->extractor->getDefinitionByValue(42);
        $this->extractor->getDefinitionByValue(42.0);
        $this->extractor->getDefinitionByValue([1,2,3]);
        $this->extractor->getDefinitionByValue((object)[1,2,3]);
    }
}
