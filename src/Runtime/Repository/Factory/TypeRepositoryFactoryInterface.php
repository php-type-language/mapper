<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository\Factory;

use TypeLang\Mapper\Context\Direction;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Runtime\Configuration;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

/**
 * Responsible for the initialization logic of the {@see TypeRepositoryInterface}.
 *
 * ```
 * // List of normalization "empty" platform's types
 * $types = $factory->createTypeRepository($config, new EmptyPlatform(), Direction::Normalize);
 * ```
 */
interface TypeRepositoryFactoryInterface
{
    public function createTypeRepository(
        Configuration $config,
        PlatformInterface $platform,
        TypeParserInterface $parser,
        Direction $direction,
    ): TypeRepositoryInterface;
}
