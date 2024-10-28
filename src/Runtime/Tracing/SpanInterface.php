<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Tracing;

interface SpanInterface
{
    /**
     * Sets a single attribute on the span.
     *
     * @param non-empty-string $key
     */
    public function setAttribute(string $key, mixed $value): void;

    /**
     * Marks this span as completed.
     */
    public function stop(): void;
}
