<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\MapAlias;
use TypeLang\Mapper\Mapping\Metadata\AliasConflict;

final class NameOrLastAliasStub
{
    #[MapAlias(['mail', 'e_mail'], onConflict: AliasConflict::UseNameOrLast)]
    public string $email;
}
