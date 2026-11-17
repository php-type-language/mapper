<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\MapAlias;
use TypeLang\Mapper\Mapping\MapName;
use TypeLang\Mapper\Mapping\Metadata\AliasConflict;

final class AliasedStub
{
    #[MapName('e-mail')]
    #[MapAlias(['mail', 'email'])]
    public string $email;

    #[MapAlias(['login'], onConflict: AliasConflict::UseFirst)]
    public string $name;

    #[MapAlias(['years'])]
    public int $age = 0;
}
