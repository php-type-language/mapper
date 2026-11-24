<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

/**
 * Private property of the name of the private property of the parent.
 */
final class ShadowSecretStub extends PlainSecretStub
{
    private string $secret = 'child';

    public function getChildSecret(): string
    {
        return $this->secret;
    }
}
