<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Attribute;

use TypeLang\Mapper\Coercer\CoercerInterface;

/**
 * Sets the autoconfigured {@see CoercerInterface} service as the coercer of
 * the type in mappers, as the `coercers` key of the config. A mapper takes
 * one coercer per type: two for the same type and mapper are an error.
 *
 * ```
 * #[AsMapperCoercer('float')]
 * final class DecimalCommaCoercer implements CoercerInterface {}
 * ```
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class AsMapperCoercer
{
    public function __construct(
        /**
         * Type the coercer reads: `int`, `float`, `string`, `bool` or
         * `array` (input of the arrays, the lists and the objects)
         *
         * @var non-empty-string
         */
        public readonly string $type,
        /**
         * Names of the mappers the service extends, the default mapper and
         * all named ones inheriting it in case of an empty list
         *
         * @var list<non-empty-string>
         */
        public readonly array $mappers = [],
    ) {}
}
