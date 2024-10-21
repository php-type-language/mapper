<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Unit\Type;

use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Type\BoolType;
use TypeLang\Mapper\Type\TypeInterface;

#[Group('type-lang/mapper')]
final class BoolTypeTest extends TypeTestCase
{
    protected function getType(): TypeInterface
    {
        return new BoolType();
    }

    protected function getCastExpectation(mixed $value, ValueType $type, Context $ctx): mixed
    {
        return match ($type) {
            ValueType::String,
            ValueType::IntNumericString,
            ValueType::NegativeIntNumericString,
            ValueType::FloatNumericString,
            ValueType::NegativeFloatNumericString,
            ValueType::ExponentNumericString,
            ValueType::NegativeExponentNumericString => $this->expectCastIfNonStrict(true, $ctx),
            ValueType::Null => $this->expectCastIfNonStrict(false, $ctx),
            ValueType::Int,
            ValueType::NegativeInt => $this->expectCastIfNonStrict(true, $ctx),
            ValueType::True => true,
            ValueType::False => false,
            ValueType::Float,
            ValueType::AroundZeroFloat,
            ValueType::AroundOneFloat,
            ValueType::ExponentFloat,
            ValueType::InfFloat,
            ValueType::NegativeInfFloat,
            ValueType::NanFloat,
            ValueType::Object,
            ValueType::StringableObject,
            ValueType::Array => $this->expectCastIfNonStrict(true, $ctx),
            ValueType::EmptyArray => $this->expectCastIfNonStrict(false, $ctx),
            ValueType::StringBackedEnum,
            ValueType::IntBackedEnum => $this->expectCastIfNonStrict(true, $ctx),
            ValueType::UnitEnum => $this->expectCastIfNonStrict(true, $ctx),
        };
    }
}
