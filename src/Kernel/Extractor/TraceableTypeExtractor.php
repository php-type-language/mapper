<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Extractor;

use TypeLang\Mapper\Kernel\Tracing\SpanInterface;
use TypeLang\Mapper\Kernel\Tracing\TracerInterface;

final class TraceableTypeExtractor implements TypeExtractorInterface
{
    public function __construct(
        private readonly TracerInterface $tracer,
        private readonly TypeExtractorInterface $delegate,
    ) {}

    private function start(mixed $value): SpanInterface
    {
        return $this->tracer->start(\sprintf('Infer "%s"', \var_export($value, true)));
    }

    public function getDefinitionByValue(mixed $value): string
    {
        $span = $this->start($value);

        try {
            return $this->delegate->getDefinitionByValue($value);
        } finally {
            $span->stop();
        }
    }
}
