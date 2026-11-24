<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

/**
 * How an enum is read from the data: the other values of the data
 * denormalized to its cases.
 *
 * @template T of \UnitEnum
 */
final class EnumMetadata
{
    public function __construct(
        /**
         * @var class-string<T>
         */
        public readonly string $name,
        /**
         * Values of the cases, names of the cases of an enum without values,
         * by the other values of the data denormalized to them
         *
         * @var array<array-key, int|string>
         */
        public readonly array $aliases = [],
    ) {}
}
