<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context\Execution\MapperContext;

enum ValueType
{
    case String;
    case Null;
    case Int;
    case True;
    case False;
    case Float;
    case Inf;
    case Nan;
    case Object;
    case Array;
}
