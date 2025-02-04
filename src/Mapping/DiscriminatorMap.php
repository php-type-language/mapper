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
         * The property holding the type discriminator
         *
         * @var non-empty-string
         */
        public readonly string $field,
        /**
         * The mapping between field value and types, i.e.
         *
         * ```
         * [
         *     'admin_user' => AdminUser::class,
         *     'admin_users' => 'list<AdminUser>',
         * ]
         *
         * @var non-empty-array<non-empty-string, non-empty-string>
         */
        public readonly array $map,
        /**
         * Default type if the discriminator field ({@see $field}) is missing
         * or does not match the mapping rules ({@see $map})
         *
         * @var non-empty-string|null
         */
        public readonly ?string $otherwise = null,
    ) {}
}
