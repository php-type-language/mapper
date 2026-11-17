<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

/**
 * Name of the property in mapped data.
 *
 * ```
 * #[MapName('user_id')]
 * public int $userId;
 * ```
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class MapName
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $name,
    ) {}
}
