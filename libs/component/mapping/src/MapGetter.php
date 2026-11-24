<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

/**
 * Reads the field of the normalization. The position chooses the access: a
 * property is read through its backing store, its `get` hook through the
 * hook, a method by calling it. Without {@see MapSetter} next to it the
 * member is not denormalized.
 *
 * ```
 * #[MapGetter('created_at')]
 * public \DateTimeImmutable $createdAt;
 *
 * public string $title {
 *     #[MapGetter]
 *     get => \ucfirst($this->title);
 * }
 *
 * #[MapGetter('full_name')]
 * public function getFullName(): string
 * ```
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
final class MapGetter
{
    public function __construct(
        /**
         * Name of the field in the data, the name of the property in case
         * of {@see null}, required on a method
         *
         * @var non-empty-string|null
         */
        public readonly ?string $name = null,
        /**
         * Type of the read value, the type of the access in case of
         * {@see null}
         *
         * @var non-empty-string|null
         */
        public readonly ?string $type = null,
    ) {}
}
