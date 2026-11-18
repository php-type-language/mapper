<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Attribute;

use TypeLang\Mapper\Type\Expander\TypeExpanderInterface;

/**
 * Adds the autoconfigured {@see TypeExpanderInterface} service to mappers,
 * tried before the default expanders.
 *
 * ```
 * #[AsMapperTypeExpander]
 * final class UuidTypeExpander implements TypeExpanderInterface {}
 * ```
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class AsMapperTypeExpander extends MapperExtensionAttribute {}
