<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

use TypeLang\Mapper\Mapping\Metadata\AliasConflict;

/**
 * Writes the field of the denormalization. The position chooses the access:
 * a property is written to its backing store, its `set` hook through the
 * hook, a method taking one value by calling it, its result is not used.
 * Without {@see MapGetter} next to it the member is not normalized.
 *
 * ```
 * #[MapSetter('password', onUndefinedError: 'Password is required')]
 * public string $password;
 *
 * #[MapSetter('name', aliases: ['title'])]
 * public function setName(string $name): void
 * ```
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
final class MapSetter
{
    /**
     * Other names of the field accepted on denormalization
     *
     * @var list<non-empty-string>
     */
    public readonly array $aliases;

    /**
     * @param non-empty-string|list<non-empty-string> $aliases one alias or
     *        several of them
     */
    public function __construct(
        /**
         * Name of the field in the data, the name of the property in case
         * of {@see null}, required on a method
         *
         * @var non-empty-string|null
         */
        public readonly ?string $name = null,
        string|array $aliases = [],
        /**
         * Type of the written value, the type of the access in case of
         * {@see null}
         *
         * @var non-empty-string|null
         */
        public readonly ?string $type = null,
        /**
         * Whether the value is taken without coercion, {@see null} in case
         * of the configuration of the mapper
         */
        public readonly ?bool $strict = null,
        /**
         * What the field takes when the data has several of its names,
         * {@see AliasConflict::DEFAULT} in case of {@see null}
         */
        public readonly ?AliasConflict $onConflict = null,
        /**
         * Template of the message of a value of a wrong type
         *
         * @var non-empty-string|null
         */
        public readonly ?string $onTypeError = null,
        /**
         * Template of the message of a missing field
         *
         * @var non-empty-string|null
         */
        public readonly ?string $onUndefinedError = null,
    ) {
        if (!\is_array($aliases)) {
            $aliases = [$aliases];
        }

        $this->aliases = $aliases;
    }
}
