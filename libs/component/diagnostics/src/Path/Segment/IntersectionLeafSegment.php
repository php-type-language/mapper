<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Path\Segment;

/**
 * Step into a member of an intersection type.
 *
 * ```
 *  Countable&Traversable
 *            ^^^^^^^^^^^ the member at index 1
 * ```
 */
final class IntersectionLeafSegment implements SegmentInterface
{
    public function __construct(
        /**
         * Position of the member in the intersection, from zero
         *
         * @var int<0, max>
         */
        public readonly int $index,
    ) {}
}
