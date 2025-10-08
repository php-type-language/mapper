<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader\Stub;

use TypeLang\Mapper\Mapping\NormalizeAsArray;

#[NormalizeAsArray(enabled: false)]
final class ClassWithNormalizeAsArrayDisabled
{
    public int $id;
}
