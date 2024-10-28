<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository;

use TypeLang\Mapper\Runtime\Repository\TraceableTypeRepository\TraceableType;
use TypeLang\Mapper\Runtime\Tracing\TracerInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class TraceableTypeRepositoryRuntime implements TypeRepositoryRuntimeInterface
{
    public function __construct(
        private readonly TracerInterface $tracer,
        private readonly TypeRepositoryRuntimeInterface $delegate,
    ) {}

    public function getTypeByStatement(TypeStatement $statement, ?\ReflectionClass $context = null): TypeInterface
    {
        $span = $this->tracer->start(\sprintf('Fetching by statement "%s"', \get_debug_type($statement)));

        try {
            $span->setAttribute('value', $statement);

            $result = $this->delegate->getTypeByStatement($statement, $context);

            $span->setAttribute('result', $result);
        } finally {
            $span->stop();
        }

        if ($result instanceof TraceableType) {
            return $result;
        }

        return new TraceableType($this->tracer, $result);
    }
}
