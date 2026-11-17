<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

enum StatusStub: string
{
    case Active = 'active';
    case Banned = 'banned';
}
