<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

/**
 * Maps the property in one direction only, it is left out of the other one.
 *
 * ```
 * #[MapOnly(Direction::Normalization)]
 * public readonly \DateTimeImmutable $createdAt;
 * ```
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class MapOnly
{
    public function __construct(
        public readonly Direction $direction,
    ) {}
}
