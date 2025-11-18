<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Repository\Factory;

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Context\DirectionInterface;
use TypeLang\Mapper\Context\MapperContext;
use TypeLang\Mapper\Kernel\Repository\DecorateByCoercibleTypeRepository;
use TypeLang\Mapper\Kernel\Repository\DecorateByLoggableTypeRepository;
use TypeLang\Mapper\Kernel\Repository\DecorateByTraceableTypeRepository;
use TypeLang\Mapper\Kernel\Repository\InMemoryTypeRepository;
use TypeLang\Mapper\Kernel\Repository\LoggableTypeRepository;
use TypeLang\Mapper\Kernel\Repository\TraceableTypeRepository;
use TypeLang\Mapper\Kernel\Repository\TypeRepository;
use TypeLang\Mapper\Kernel\Repository\TypeRepositoryInterface;
use TypeLang\Printer\PrettyPrinter;
use TypeLang\Printer\PrinterInterface;

/**
 * Implements the basic (built-in) logic for initializing the
 * {@see TypeRepositoryInterface} instance.
 */
final class DefaultTypeRepositoryFactory implements TypeRepositoryFactoryInterface
{
    public const DEFAULT_TRACING_OPTION = false;
    public const DEFAULT_TYPE_CAST_TRACING_OPTION = false;
    public const DEFAULT_TYPE_MATCH_TRACING_OPTION = true;
    public const DEFAULT_LOGGING_OPTION = false;
    public const DEFAULT_TYPE_CAST_LOGGING_OPTION = false;
    public const DEFAULT_TYPE_MATCH_LOGGING_OPTION = true;
    public const DEFAULT_MEMOIZATION_OPTION = true;

    public function __construct(
        private readonly bool $enableLogging = self::DEFAULT_LOGGING_OPTION,
        private readonly bool $enableTypeMatchLogging = self::DEFAULT_TYPE_MATCH_TRACING_OPTION,
        private readonly bool $enableTypeCastLogging = self::DEFAULT_TYPE_CAST_TRACING_OPTION,
        private readonly bool $enableTracing = self::DEFAULT_TRACING_OPTION,
        private readonly bool $enableTypeMatchTracing = self::DEFAULT_TYPE_MATCH_LOGGING_OPTION,
        private readonly bool $enableTypeCastTracing = self::DEFAULT_TYPE_CAST_LOGGING_OPTION,
        private readonly bool $enableMemoization = self::DEFAULT_MEMOIZATION_OPTION,
        private readonly PrinterInterface $typeTracingPrinter = new PrettyPrinter(
            wrapUnionType: false,
            multilineShape: \PHP_INT_MAX,
        ),
    ) {}

    public function createTypeRepository(MapperContext $context, DirectionInterface $direction): TypeRepositoryInterface
    {
        $types = new TypeRepository(
            context: $context,
            direction: $direction,
            builders: $context->platform->getTypes(),
        );

        if ($this->enableTypeCastTracing || $this->enableTypeMatchTracing) {
            $types = $this->withTypeTracing($types, $context->config);
        }

        if ($this->enableTracing) {
            $types = $this->withTracing($types, $context->config);
        }

        if ($this->enableTypeCastLogging || $this->enableTypeMatchLogging) {
            $types = $this->withTypeLogging($types, $context->config);
        }

        if ($this->enableLogging) {
            $types = $this->withLogging($types, $context->config);
        }

        $types = new DecorateByCoercibleTypeRepository(
            delegate: $types,
            coercers: $context->platform->getTypeCoercers(),
        );

        if ($this->enableMemoization) {
            $types = $this->withMemoization($types);
        }

        return $types;
    }

    private function withTypeTracing(TypeRepositoryInterface $types, Configuration $config): TypeRepositoryInterface
    {
        $tracer = $config->findTracer();

        if ($tracer === null) {
            return $types;
        }

        return new DecorateByTraceableTypeRepository(
            enableTypeMatchTracing: $this->enableTypeMatchTracing,
            enableTypeCastTracing: $this->enableTypeCastTracing,
            printer: $this->typeTracingPrinter,
            delegate: $types,
        );
    }

    private function withTracing(TypeRepositoryInterface $types, Configuration $config): TypeRepositoryInterface
    {
        $tracer = $config->findTracer();

        if ($tracer === null) {
            return $types;
        }

        return new TraceableTypeRepository($tracer, $types);
    }

    private function withTypeLogging(TypeRepositoryInterface $types, Configuration $config): TypeRepositoryInterface
    {
        $logger = $config->findLogger();

        if ($logger === null) {
            return $types;
        }

        return new DecorateByLoggableTypeRepository(
            enableTypeMatchLogging: $this->enableTypeMatchLogging,
            enableTypeCastLogging: $this->enableTypeCastLogging,
            delegate: $types,
        );
    }

    private function withLogging(TypeRepositoryInterface $types, Configuration $config): TypeRepositoryInterface
    {
        $logger = $config->findLogger();

        if ($logger === null) {
            return $types;
        }

        return new LoggableTypeRepository($logger, $types);
    }

    private function withMemoization(TypeRepositoryInterface $types): InMemoryTypeRepository
    {
        return new InMemoryTypeRepository($types);
    }
}
