<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

/**
 * Reads the property on normalization through the method: the `get` hook
 * of the property, or a public method without required arguments that
 * names the property. The read type is the return type of the method,
 * {@see MapType} on the method overrides it.
 *
 * ```
 * #[MapGetter('name')]
 * public function getName(): string
 * ```
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class MapGetter
{
    public function __construct(
        /**
         * Name of the property, {@see null} on a hook
         *
         * @var non-empty-string|null
         */
        public readonly ?string $property = null,
    ) {}
}
