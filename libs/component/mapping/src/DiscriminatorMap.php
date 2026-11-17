<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

/**
 * Polymorphic class: the field of the data names the type the data is
 * mapped to. Names in the types are resolved by the imports of the file the
 * attribute is written in.
 *
 * ```
 * #[DiscriminatorMap(field: 'type', map: [
 *     'circle' => Circle::class,
 *     'square' => Square::class,
 * ])]
 * abstract class Shape {}
 * ```
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class DiscriminatorMap
{
    public function __construct(
        /**
         * Key of the data holding the tag
         *
         * @var non-empty-string
         */
        public readonly string $field,
        /**
         * Types by their tags
         *
         * @var non-empty-array<array-key, non-empty-string>
         */
        public readonly array $map,
        /**
         * Type of the data with no tag or with an unknown one, {@see null}
         * in case of such data is an error
         *
         * @var non-empty-string|null
         */
        public readonly ?string $otherwise = null,
    ) {}
}
