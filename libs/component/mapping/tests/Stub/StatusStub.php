<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

enum StatusStub: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
