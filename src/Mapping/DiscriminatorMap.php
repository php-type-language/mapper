<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

/**
 * ```
 * #[DiscriminatorMap(field: 'type', map: [
 *     'admin' => Admin::class,
 *     'moderator' => Moderator::class,
 *     'user' => User::class,
 *     'any' => 'array<array-key, string>'
 * ])]
 * abstract class Account {}
 *
 * final class Admin extends Account {}
 * final class Moderator extends Account {}
 * final class User extends Account {}
 * ```
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class DiscriminatorMap
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $field,
        /**
         * @var non-empty-array<non-empty-string, non-empty-string>
         */
        public readonly array $map,
    ) {}
}
