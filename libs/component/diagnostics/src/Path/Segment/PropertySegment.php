<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Path\Segment;

/**
 * Step into a property of an object or into a named field of its data.
 */
final class PropertySegment implements SegmentInterface
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $property,
    ) {}
}
