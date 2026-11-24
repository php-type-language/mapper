<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapSetter;

/**
 * Private property with the attributes of the access, mapped in the
 * children too.
 */
class ParentSecretStub
{
    /**
     * @var non-empty-string
     */
    #[MapGetter('secret'), MapSetter]
    private string $secret = 'base';

    public function getSecret(): string
    {
        return $this->secret;
    }
}
