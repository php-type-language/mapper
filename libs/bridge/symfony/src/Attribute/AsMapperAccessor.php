<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Attribute;

use TypeLang\Mapper\PropertyAccessor\Strategy\AccessStrategyInterface;

/**
 * Adds the autoconfigured {@see AccessStrategyInterface} service to mappers,
 * tried before the default property access strategies.
 *
 * ```
 * #[AsMapperAccessor]
 * final class GetterSetterStrategy implements AccessStrategyInterface {}
 * ```
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class AsMapperAccessor extends MapperExtensionAttribute {}
