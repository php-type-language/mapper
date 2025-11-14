<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Repository\Factory;

use TypeLang\Mapper\Context\DirectionInterface;
use TypeLang\Mapper\Context\MapperContext;
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
        MapperContext $context,
        DirectionInterface $direction,
    ): TypeRepositoryInterface;
}
