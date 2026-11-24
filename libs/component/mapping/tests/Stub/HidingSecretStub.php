<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapGetter;

/**
 * Private property of the name of the private property of the parent, a
 * member of its own.
 */
final class HidingSecretStub extends ParentSecretStub
{
    #[MapGetter('child_secret')]
    private string $secret = 'child';
}
