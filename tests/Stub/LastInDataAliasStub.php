<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\MapAlias;
use TypeLang\Mapper\Mapping\Metadata\AliasConflict;

final class LastInDataAliasStub
{
    #[MapAlias('mail', onConflict: AliasConflict::UseLast)]
    public string $email;
}
