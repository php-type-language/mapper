<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Path;

use TypeLang\Mapper\Diagnostics\Path\Segment\SegmentInterface;

/**
 * Location of a value inside the mapped input and the type it is mapped by,
 * from the root.
 */
final class Path
{
    public function __construct(
        /**
         * Segments from the root, an empty list is the root itself
         *
         * @var list<SegmentInterface>
         */
        public readonly array $segments = [],
    ) {}
}
