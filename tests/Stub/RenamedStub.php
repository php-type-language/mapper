<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\MapName;

final class RenamedStub
{
    #[MapName('e-mail')]
    public string $email;

    public string $name;
}
