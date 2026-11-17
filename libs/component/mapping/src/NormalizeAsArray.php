<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

/**
 * Whether an object of the class is normalized to an array or, in case of
 * {@see false}, to a {@see \stdClass}, over the configuration of the mapper.
 *
 * ```
 * #[NormalizeAsArray(false)]
 * final class Settings {}
 * ```
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class NormalizeAsArray
{
    public function __construct(
        public readonly bool $enabled = true,
    ) {}
}
