<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapAlias;
use TypeLang\Mapper\Mapping\Metadata\AliasConflict;

enum AliasConflictCaseStub: string
{
    #[MapAlias(['on'], onConflict: AliasConflict::UseFirst)]
    case Enabled = 'enabled';
}
