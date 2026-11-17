<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Path\Segment;

/**
 * Step into a branch of a union type.
 *
 * ```
 *  int|string|null
 *      ^^^^^^      the branch at index 1
 * ```
 */
final class UnionLeafSegment implements SegmentInterface
{
    public function __construct(
        /**
         * Position of the branch in the union, from zero
         *
         * @var int<0, max>
         */
        public readonly int $index,
    ) {}
}
