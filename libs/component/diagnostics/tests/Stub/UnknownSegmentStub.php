<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Tests\Stub;

use TypeLang\Mapper\Diagnostics\Path\Segment\SegmentInterface;

/**
 * Segment of neither the data nor the type family.
 */
final class UnknownSegmentStub implements SegmentInterface {}
