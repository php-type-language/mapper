<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

use TypeLang\Mapper\Mapping\Reader\NamingStrategyMetadataReader;
use TypeLang\Mapper\Naming\NamingStrategyInterface;

/**
 * Naming strategy of the properties of the class without an explicit
 * name, over the strategy of the mapper. A string is the name of a
 * built-in strategy (`camelCase`, `PascalCase`, `snake_case`,
 * `SCREAMING_SNAKE_CASE`, `kebab-case`) or the class of a strategy without
 * required constructor arguments. The names are given by
 * {@see NamingStrategyMetadataReader}.
 *
 * ```
 * #[NamingStrategy('snake_case')]
 * final class User {}
 *
 * #[NamingStrategy(new PrefixNamingStrategy('user_'))]
 * final class Account {}
 * ```
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class NamingStrategy
{
    public function __construct(
        /**
         * @var NamingStrategyInterface|non-empty-string
         */
        public readonly NamingStrategyInterface|string $strategy,
    ) {}
}
