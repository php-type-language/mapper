<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Parser;

use TypeLang\Mapper\Runtime\Tracing\TracerInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class TraceableTypeParser implements TypeParserInterface
{
    public function __construct(
        private readonly TracerInterface $tracer,
        private readonly TypeParserInterface $delegate,
    ) {}

    public function getStatementByDefinition(string $definition): TypeStatement
    {
        $span = $this->tracer->start(\sprintf('Parse "%s"', $definition));

        try {
            return $this->delegate->getStatementByDefinition($definition);
        } finally {
            $span->stop();
        }
    }
}
