<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Attribute;

use TypeLang\Mapper\Type\Decorator\TypeDecoratorInterface;

/**
 * Adds the autoconfigured {@see TypeDecoratorInterface} service to mappers.
 *
 * ```
 * #[AsMapperDecorator]
 * final class TrimmingDecorator implements TypeDecoratorInterface {}
 * ```
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class AsMapperDecorator extends MapperExtensionAttribute {}
