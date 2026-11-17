<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Attribute;

use TypeLang\Mapper\Hook\ClassHookInterface;

/**
 * Adds the autoconfigured {@see ClassHookInterface} service to mappers.
 *
 * ```
 * #[AsMapperHook]
 * final class SlugHook implements BeforeDenormalizationHookInterface {}
 * ```
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class AsMapperHook extends MapperExtensionAttribute {}
