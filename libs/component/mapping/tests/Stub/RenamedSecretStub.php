<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapName;

/**
 * Private property with a helper attribute only, mapped by a selector of
 * private properties.
 */
class RenamedSecretStub
{
    #[MapName('renamed')]
    private string $secret = 'base';

    public function getSecret(): string
    {
        return $this->secret;
    }
}
