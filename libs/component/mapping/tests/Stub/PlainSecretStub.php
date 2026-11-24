<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

/**
 * Private, protected and static properties without attributes.
 */
class PlainSecretStub
{
    protected string $prot = 'prot';

    private string $secret = 'base';

    private static string $counter = 'parent';

    public function getSecret(): string
    {
        return $this->secret;
    }
}
