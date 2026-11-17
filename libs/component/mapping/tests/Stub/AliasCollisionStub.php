<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapAlias;

final class AliasCollisionStub
{
    public string $name;

    #[MapAlias(['name'])]
    public string $title;

    #[MapAlias(['code', 'code'])]
    public string $code;
}
