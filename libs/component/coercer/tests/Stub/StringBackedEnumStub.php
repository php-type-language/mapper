<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Coercer\Tests\Stub;

enum StringBackedEnumStub: string
{
    case Example = '42';
}
