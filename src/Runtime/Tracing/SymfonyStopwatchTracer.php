<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Tracing;

use Symfony\Component\Stopwatch\Stopwatch;
use TypeLang\Mapper\Runtime\Tracing\SymfonyStopwatchTracer\SymfonyStopwatchSpan;

final class SymfonyStopwatchTracer implements TracerInterface
{
    public function __construct(
        private readonly Stopwatch $stopwatch,
    ) {}

    public function start(string $name): SpanInterface
    {
        return new SymfonyStopwatchSpan(
            event: $this->stopwatch->start($name),
        );
    }
}
