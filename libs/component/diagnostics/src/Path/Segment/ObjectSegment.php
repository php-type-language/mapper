<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Path\Segment;

/**
 * Step into an object of a class, the following property segments are
 * properties of this class.
 */
final class ObjectSegment implements SegmentInterface
{
    public function __construct(
        /**
         * @var class-string
         */
        public readonly string $class,
    ) {}
}
