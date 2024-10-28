<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Runtime\Repository\TraceableTypeRepository\TraceableType;
use TypeLang\Mapper\Runtime\Tracing\TracerInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class TraceableTypeRepository extends TypeRepositoryDecorator
{
    public function __construct(
        private readonly TracerInterface $tracer,
        TypeRepositoryInterface $delegate,
    ) {
        parent::__construct($delegate);
    }

    #[\Override]
    public function getTypeByDefinition(
        #[Language('PHP')]
        string $definition,
        ?\ReflectionClass $context = null,
    ): TypeInterface {
        $span = $this->tracer->start(\sprintf('Fetching by definition [%s]', $definition));

        try {
            $span->setAttribute('value', $definition);

            $result = parent::getTypeByDefinition($definition, $context);

            $span->setAttribute('result', $result);
        } finally {
            $span->stop();
        }

        if ($result instanceof TraceableType) {
            return $result;
        }

        return new TraceableType($this->tracer, $result);
    }

    #[\Override]
    public function getTypeByValue(mixed $value, ?\ReflectionClass $context = null): TypeInterface
    {
        $span = $this->tracer->start(\sprintf('Fetching by value [%s]', \get_debug_type($value)));

        try {
            $span->setAttribute('value', $value);

            $result = parent::getTypeByValue($value, $context);

            $span->setAttribute('result', $result);
        } finally {
            $span->stop();
        }

        if ($result instanceof TraceableType) {
            return $result;
        }

        return new TraceableType($this->tracer, $result);
    }

    #[\Override]
    public function getTypeByStatement(TypeStatement $statement, ?\ReflectionClass $context = null): TypeInterface
    {
        $span = $this->tracer->start(\sprintf('Fetching by statement "%s"', \get_debug_type($statement)));

        try {
            $span->setAttribute('value', $statement);

            $result = parent::getTypeByStatement($statement, $context);

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
