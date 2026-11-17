<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Attribute;

use TypeLang\Mapper\Instantiator\Strategy\InstantiationStrategyInterface;

/**
 * Adds the autoconfigured {@see InstantiationStrategyInterface} service to
 * mappers, tried before creating the objects without the constructor.
 *
 * ```
 * #[AsMapperInstantiator]
 * final class EntityStrategy implements InstantiationStrategyInterface {}
 * ```
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class AsMapperInstantiator extends MapperExtensionAttribute {}
