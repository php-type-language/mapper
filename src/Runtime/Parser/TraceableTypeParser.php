<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Parser;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Runtime\Tracing\TracerInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class TraceableTypeParser implements TypeParserInterface
{
    public function __construct(
        private readonly TracerInterface $tracer,
        private readonly TypeParserInterface $delegate,
    ) {}

    public function getStatementByDefinition(#[Language('PHP')] string $definition): TypeStatement
    {
        $span = $this->tracer->start('type-lang::parsing');

        try {
            return $this->delegate->getStatementByDefinition($definition);
        } finally {
            $span->stop();
        }
    }

    public function getStatementByValue(mixed $value): TypeStatement
    {
        $span = $this->tracer->start('type-lang::parsing');

        try {
            return $this->delegate->getStatementByValue($value);
        } finally {
            $span->stop();
        }
    }
}
