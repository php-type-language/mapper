<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapAlias;
use TypeLang\Mapper\Mapping\Metadata\AliasConflict;

final class ConflictingAliasStub
{
    #[MapAlias('mail', onConflict: AliasConflict::Fail)]
    #[MapAlias('e_mail', onConflict: AliasConflict::UseFirst)]
    public string $email;
}
