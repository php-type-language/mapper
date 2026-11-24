<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

/**
 * Type of the property, names are resolved by the imports of the file the
 * attribute is written in.
 *
 * ```
 * #[MapType('list<Address>')]
 * public array $addresses;
 * ```
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class MapType
{
    public function __construct(
        /**
         * Type of both reading and writing
         *
         * @var non-empty-string
         */
        public readonly string $type,
        /**
         * Whether a value must match the type exactly, without coercion,
         * {@see null} means the mapper configuration
         */
        public readonly ?bool $strict = null,
    ) {}
}
