<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Repository\Factory;

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Context\DirectionInterface;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;
use TypeLang\Mapper\Type\Repository\TypeRepositoryInterface;

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
        DirectionInterface $direction,
    ): TypeRepositoryInterface;
}
