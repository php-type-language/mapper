<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\NormalizeAsArray;

#[NormalizeAsArray(false)]
final class ObjectFormStub
{
    public string $theme = 'dark';
}
