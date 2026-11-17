<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Path\Segment;

/**
 * Step into a key of an associative array or an index of a list.
 */
final class KeySegment implements SegmentInterface
{
    public function __construct(
        public readonly int|string $key,
    ) {}
}
