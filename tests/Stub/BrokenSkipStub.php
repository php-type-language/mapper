<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\SkipWhen;

final class BrokenSkipStub
{
    #[SkipWhen('this.name.length')]
    public string $name = 'John';
}
