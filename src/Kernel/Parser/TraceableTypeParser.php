<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Parser;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Kernel\Tracing\SpanInterface;
use TypeLang\Mapper\Kernel\Tracing\TracerInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class TraceableTypeParser implements TypeParserInterface
{
    public function __construct(
        private readonly TracerInterface $tracer,
        private readonly TypeParserInterface $delegate,
    ) {}

    private function start(string $definition): SpanInterface
    {
        return $this->tracer->start(\sprintf('Parse "%s"', $definition));
    }

    public function getStatementByDefinition(#[Language('PHP')] string $definition): TypeStatement
    {
        $span = $this->start($definition);

        try {
            return $this->delegate->getStatementByDefinition($definition);
        } finally {
            $span->stop();
        }
    }
}
