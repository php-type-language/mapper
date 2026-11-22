<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

/**
 * Writes the property on denormalization through the method: the `set` hook
 * of the property, or a public method taking one value that names the
 * property, its result is not used. The write type is the type of the
 * parameter, {@see MapType} on the method overrides it.
 *
 * ```
 * #[MapSetter('name')]
 * public function setName(string $name): void
 * ```
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class MapSetter
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
