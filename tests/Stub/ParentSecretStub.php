<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapSetter;

class ParentSecretStub
{
    #[MapGetter, MapSetter]
    private string $secret = 'base';

    public function getSecret(): string
    {
        return $this->secret;
    }
}
