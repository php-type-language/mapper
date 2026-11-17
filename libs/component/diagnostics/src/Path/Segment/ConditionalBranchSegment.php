<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Path\Segment;

use TypeLang\Mapper\Diagnostics\Path\Segment\ConditionalBranchSegment\ConditionalBranch;

/**
 * Step into a branch of a conditional type.
 *
 * ```
 *  ($value is int ? string : bool)
 *                   ^^^^^^         the Then branch
 *                            ^^^^  the Else branch
 * ```
 */
final class ConditionalBranchSegment implements SegmentInterface
{
    public function __construct(
        public readonly ConditionalBranch $branch,
    ) {}
}
