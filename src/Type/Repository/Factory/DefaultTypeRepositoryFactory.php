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
    public function createTypeRepository(
        MapperContext $context,
        PlatformInterface $platform,
        DirectionInterface $direction,
    ): TypeRepositoryInterface {
        $types = $this->createDefaultRepository($context, $direction, $platform);

        $types = $this->withTypeTracing($types, $context->config);
        $types = $this->withTracing($types, $context->config);
        $types = $this->withTypeLogging($types, $context->config);
        $types = $this->withLogging($types, $context->config);
        $types = $this->withCoercers($types, $platform, $direction);

        return $this->withMemoization($types);
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

        if (!$config->shouldTraceTypeMatch() && !$config->shouldTraceTypeCast()) {
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

        if (!$config->shouldTraceTypeFind()) {
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

        if (!$config->shouldLogTypeCast() && !$config->shouldLogTypeMatch()) {
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

        if (!$config->shouldLogTypeFind()) {
            return $types;
        }

        return new LoggableTypeRepository($logger, $types);
    }

    private function withMemoization(TypeRepositoryInterface $types): InMemoryTypeRepository
    {
        return new InMemoryTypeRepository($types);
    }
}
