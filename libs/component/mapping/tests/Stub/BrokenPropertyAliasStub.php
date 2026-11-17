<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapAlias;

final class BrokenPropertyAliasStub
{
    #[MapAlias(['mail', ''])]
    public string $email;
}
