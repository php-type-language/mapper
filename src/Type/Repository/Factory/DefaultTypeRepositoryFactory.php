<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Repository\Factory;

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Context\DirectionInterface;
use TypeLang\Mapper\Context\MapperContext;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Type\Repository\DecorateByCoercibleTypeRepository;
use TypeLang\Mapper\Type\Repository\DecorateByLoggableTypeRepository;
use TypeLang\Mapper\Type\Repository\DecorateByTraceableTypeRepository;
use TypeLang\Mapper\Type\Repository\InMemoryTypeRepository;
use TypeLang\Mapper\Type\Repository\LoggableTypeRepository;
use TypeLang\Mapper\Type\Repository\TraceableTypeRepository;
use TypeLang\Mapper\Type\Repository\TypeRepository;
use TypeLang\Mapper\Type\Repository\TypeRepositoryInterface;

/**
 * Implements the basic (built-in) logic for initializing the
 * {@see TypeRepositoryInterface} instance.
 */
final class DefaultTypeRepositoryFactory implements TypeRepositoryFactoryInterface
{
    public const DEFAULT_TRACING_OPTION = false;
    public const DEFAULT_TYPE_TRACING_OPTION = true;
    public const DEFAULT_LOGGING_OPTION = false;
    public const DEFAULT_TYPE_LOGGING_OPTION = true;
    public const DEFAULT_MEMOIZATION_OPTION = true;

    public function __construct(
        private readonly bool $enableLogging = self::DEFAULT_LOGGING_OPTION,
        private readonly bool $enableTypeLogging = self::DEFAULT_TYPE_LOGGING_OPTION,
        private readonly bool $enableTracing = self::DEFAULT_TRACING_OPTION,
        private readonly bool $enableTypeTracing = self::DEFAULT_TYPE_TRACING_OPTION,
        private readonly bool $enableMemoization = self::DEFAULT_MEMOIZATION_OPTION,
    ) {}

    public function createTypeRepository(
        MapperContext $context,
        PlatformInterface $platform,
        DirectionInterface $direction,
    ): TypeRepositoryInterface {
        $types = $this->createDefaultRepository($context, $direction, $platform);

        if ($this->enableTypeTracing) {
            $types = $this->withTypeTracing($types, $context->config);
        }

        if ($this->enableTracing) {
            $types = $this->withTracing($types, $context->config);
        }

        if ($this->enableTypeLogging) {
            $types = $this->withTypeLogging($types, $context->config);
        }

        if ($this->enableLogging) {
            $types = $this->withLogging($types, $context->config);
        }

        $types = $this->withCoercers($types, $platform, $direction);

        if ($this->enableMemoization) {
            $types = $this->withMemoization($types);
        }

        return $types;
    }

    private function createDefaultRepository(
        MapperContext $context,
        DirectionInterface $direction,
        PlatformInterface $platform,
    ): TypeRepository {
        return new TypeRepository(
            context: $context,
            direction: $direction,
            builders: $platform->getTypes($direction),
        );
    }

    private function withCoercers(
        TypeRepositoryInterface $types,
        PlatformInterface $platform,
        DirectionInterface $direction,
    ): TypeRepositoryInterface {
        return new DecorateByCoercibleTypeRepository(
            delegate: $types,
            coercers: $platform->getTypeCoercers($direction),
        );
    }

    private function withTypeTracing(TypeRepositoryInterface $types, Configuration $config): TypeRepositoryInterface
    {
        $tracer = $config->findTracer();

        if ($tracer === null) {
            return $types;
        }

        return new DecorateByTraceableTypeRepository($types);
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

        return new DecorateByLoggableTypeRepository($types);
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
