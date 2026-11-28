<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

/**
 * A rejected value of the property is hidden from the errors of the
 * denormalization, as of a constructor parameter with a
 * {@see \SensitiveParameter}.
 *
 * ```
 * #[Sensitive]
 * public string $password;
 * ```
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Sensitive
{
    public function __construct(
        public readonly bool $enabled = true,
    ) {}
}
