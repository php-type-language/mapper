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

    public function getTypeByDefinition(
        #[Language('PHP')]
        string $definition,
        ?\ReflectionClass $context = null,
    ): TypeInterface {
        $span = $this->tracer->start('type-lang::fetching');

        try {
            $result = $this->delegate->getTypeByDefinition($definition, $context);
        } finally {
            $span->stop();
        }

        return new TraceableType($this->tracer, $result);
    }

    public function getTypeByValue(mixed $value, ?\ReflectionClass $context = null): TypeInterface
    {
        $span = $this->tracer->start('type-lang::fetching');

        try {
            $result = $this->delegate->getTypeByValue($value, $context);
        } finally {
            $span->stop();
        }

        return new TraceableType($this->tracer, $result);
    }

    public function getTypeByStatement(TypeStatement $statement, ?\ReflectionClass $context = null): TypeInterface
    {
        $span = $this->tracer->start('type-lang::fetching');

        try {
            $result = $this->delegate->getTypeByStatement($statement, $context);
        } finally {
            $span->stop();
        }

        return new TraceableType($this->tracer, $result);
    }
}
