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
        $span = $this->tracer->start(\sprintf('Parsing by definition [%s]', $definition));

        try {
            $span->setAttribute('value', $definition);

            $result = $this->delegate->getStatementByDefinition($definition);

            $span->setAttribute('result', $result);

            return $result;
        } finally {
            $span->stop();
        }
    }

    public function getStatementByValue(mixed $value): TypeStatement
    {
        $span = $this->tracer->start(\sprintf('Parsing by value [%s]', \get_debug_type($value)));

        try {
            $span->setAttribute('value', $value);

            $result = $this->delegate->getStatementByValue($value);

            $span->setAttribute('result', $result);

            return $result;
        } finally {
            $span->stop();
        }
    }
}
