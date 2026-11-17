<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\MapName;

final class DuplicateNameStub
{
    #[MapName('id')]
    public int $first = 1;

    #[MapName('id')]
    public int $second = 2;
}
