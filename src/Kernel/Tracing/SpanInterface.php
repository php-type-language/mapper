<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Tracing;

interface SpanInterface
{
    /**
     * Marks this span as completed.
     */
    public function stop(): void;
}
