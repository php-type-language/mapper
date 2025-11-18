<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Tracing;

interface TracerInterface
{
    /**
     * Creates a new {@see SpanInterface}.
     *
     * @param non-empty-string $name
     */
    public function start(string $name): SpanInterface;
}
