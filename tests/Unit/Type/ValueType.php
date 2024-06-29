<?php

declare(strict_types=1);

namespace Serafim\Mapper\Tests\Unit\Type;

enum ValueType
{
    case String;
    case IntNumericString;
    case NegativeIntNumericString;
    case FloatNumericString;
    case NegativeFloatNumericString;
    case ExponentNumericString;
    case NegativeExponentNumericString;
    case Null;
    case Int;
    case NegativeInt;
    case True;
    case False;
    case Float;
    case AroundZeroFloat;
    case AroundOneFloat;
    case ExponentFloat;
    case InfFloat;
    case NegativeInfFloat;
    case NanFloat;
    case Object;
    case StringableObject;
    case Array;
    case EmptyArray;
    case StringBackedEnum;
    case IntBackedEnum;
    case UnitEnum;
}
