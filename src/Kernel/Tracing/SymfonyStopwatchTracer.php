<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Tracing;

use Symfony\Component\Stopwatch\Stopwatch;
use TypeLang\Mapper\Kernel\Tracing\SymfonyStopwatchTracer\SymfonyStopwatchSpan;

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
        /**
         * Fixes a Symfony Profiler bug that cuts off all FQN classes.
         *
         * @var string $formatted
         */
        $formatted = \preg_replace('/\\\\?(?:\w+\\\\)+(\w+)/isum', '$1', $name);

        return new SymfonyStopwatchSpan(
            event: $this->stopwatch->start($formatted, $this->category),
        );
    }
}
