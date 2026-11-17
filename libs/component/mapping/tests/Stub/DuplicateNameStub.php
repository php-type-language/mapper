<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapName;

final class DuplicateNameStub
{
    #[MapName('id')]
    public int $first = 0;

    #[MapName('id')]
    public int $second = 0;

    public int $third = 0;
}
