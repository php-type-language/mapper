<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\SkipWhenNull;

/**
 * Condition of a private property that is not mapped, read in a child.
 */
class SkippedSecretStub
{
    #[SkipWhenNull]
    private ?string $note = null;
}
