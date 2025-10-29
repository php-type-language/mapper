<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Repository\Factory;

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Context\Direction;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;
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
        Direction $direction,
    ): TypeRepositoryInterface {
        $types = $this->createDefaultRepository($parser, $platform, $direction);

        $types = $this->withTracing($config, $types);
        $types = $this->withLogging($config, $types);

        return $this->withMemoization($types);
    }

    private function createDefaultRepository(
        TypeParserInterface $parser,
        PlatformInterface $platform,
        Direction $direction,
    ): TypeRepository {
        return new TypeRepository(
            parser: $parser,
            builders: $platform->getTypes($direction),
            coercers: $platform->getTypeCoercers($direction),
        );
    }

    private function withTracing(Configuration $config, TypeRepositoryInterface $types): TypeRepositoryInterface
    {
        $tracer = $config->findTracer();

        if ($tracer === null) {
            return $types;
        }

        return new TraceableTypeRepository($tracer, $types);
    }

    private function withLogging(Configuration $config, TypeRepositoryInterface $types): TypeRepositoryInterface
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
