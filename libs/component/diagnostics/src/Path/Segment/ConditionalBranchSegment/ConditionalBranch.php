<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Path\Segment\ConditionalBranchSegment;

/**
 * Branch of a conditional type `T is U ? A : B`.
 */
enum ConditionalBranch
{
    /**
     * The type `A`, chosen when the condition holds.
     */
    case Then;

    /**
     * The type `B`, chosen when the condition does not hold.
     */
    case Else;
}
