<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Repository\Factory;

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Context\DirectionInterface;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;
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
        Configuration $config,
        PlatformInterface $platform,
        TypeParserInterface $parser,
        DirectionInterface $direction,
    ): TypeRepositoryInterface {
        $types = $this->createDefaultRepository($parser, $platform, $direction);

        $types = $this->withTracing($config, $types);
        $types = $this->withLogging($config, $types);
        $types = $this->withCoercers($types, $platform, $direction);

        return $this->withMemoization($types);
    }

    private function createDefaultRepository(
        TypeParserInterface $parser,
        PlatformInterface $platform,
        DirectionInterface $direction,
    ): TypeRepository {
        return new TypeRepository(
            parser: $parser,
            builders: $platform->getTypes($direction)
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

    private function withTracing(Configuration $config, TypeRepositoryInterface $types): TypeRepositoryInterface
    {
        $tracer = $config->findTracer();

        if ($tracer === null) {
            return $types;
        }

        if ($config->shouldTraceTypeMatch() || $config->shouldTraceTypeCast()) {
            $types = new DecorateByTraceableTypeRepository($types);
        }

        if ($config->shouldTraceTypeFind()) {
            return new TraceableTypeRepository($tracer, $types);
        }

        return $types;
    }

    private function withLogging(Configuration $config, TypeRepositoryInterface $types): TypeRepositoryInterface
    {
        $logger = $config->findLogger();

        if ($logger === null) {
            return $types;
        }

        if ($config->shouldLogTypeCast() || $config->shouldLogTypeMatch()) {
            $types = new DecorateByLoggableTypeRepository($types);
        }

        if ($config->shouldLogTypeFind()) {
            return new LoggableTypeRepository($logger, $types);
        }

        return $types;
    }

    private function withMemoization(TypeRepositoryInterface $types): InMemoryTypeRepository
    {
        return new InMemoryTypeRepository($types);
    }
}
