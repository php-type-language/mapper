<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\MapName;

final class RenamedFieldStub
{
    #[MapName('user-name')]
    public string $userName;

    #[MapName('first name')]
    public string $firstName;
}
