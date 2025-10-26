<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tracing\SymfonyStopwatchTracer;

use Symfony\Component\Stopwatch\StopwatchEvent;
use TypeLang\Mapper\Tracing\SpanInterface;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Runtime\Tracing
 */
final class SymfonyStopwatchSpan implements SpanInterface
{
    public function __construct(
        private readonly StopwatchEvent $event,
    ) {}

    public function stop(): void
    {
        $this->event->stop();
    }
}
