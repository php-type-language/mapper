<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Attribute;

use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;

/**
 * Adds the autoconfigured {@see TypeBuilderInterface} service to mappers,
 * tried before the default builders.
 *
 * ```
 * #[AsMapperTypeBuilder]
 * final class UuidTypeBuilder implements TypeBuilderInterface {}
 *
 * #[AsMapperTypeBuilder(mappers: ['api'])]
 * final class MoneyTypeBuilder implements TypeBuilderInterface {}
 * ```
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class AsMapperTypeBuilder extends MapperExtensionAttribute {}
