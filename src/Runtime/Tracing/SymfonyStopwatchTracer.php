<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Tracing;

use Symfony\Component\Stopwatch\Stopwatch;
use TypeLang\Mapper\Runtime\Tracing\SymfonyStopwatchTracer\SymfonyStopwatchSpan;

final class SymfonyStopwatchTracer implements TracerInterface
{
    /**
     * @param non-empty-string|null $category
     */
    public function __construct(
        public readonly Stopwatch $stopwatch,
        private readonly ?string $category = 'type-lang',
    ) {}

    public function start(string $name): SpanInterface
    {
        return new SymfonyStopwatchSpan(
            event: $this->stopwatch->start($name, $this->category),
        );
    }
}
