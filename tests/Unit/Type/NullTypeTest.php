<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Unit\Type;

use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Context;
use TypeLang\Mapper\Type\NullType;
use TypeLang\Mapper\Type\TypeInterface;

#[Group('type-lang/mapper')]
final class NullTypeTest extends TypeTestCase
{
    protected function getType(): TypeInterface
    {
        return new NullType();
    }

    protected function getNormalizationExpectation(mixed $value, ValueType $type, Context $ctx): mixed
    {
        return match ($type) {
            ValueType::String,
            ValueType::IntNumericString,
            ValueType::NegativeIntNumericString,
            ValueType::FloatNumericString,
            ValueType::NegativeFloatNumericString,
            ValueType::ExponentNumericString,
            ValueType::NegativeExponentNumericString => $this->expectCastIfNonStrict(null, $ctx),
            ValueType::Null => null,
            ValueType::Int,
            ValueType::NegativeInt,
            ValueType::True,
            ValueType::False,
            ValueType::Float,
            ValueType::AroundZeroFloat,
            ValueType::AroundOneFloat,
            ValueType::ExponentFloat,
            ValueType::InfFloat,
            ValueType::NegativeInfFloat,
            ValueType::NanFloat,
            ValueType::Object,
            ValueType::StringableObject,
            ValueType::Array,
            ValueType::EmptyArray,
            ValueType::StringBackedEnum,
            ValueType::IntBackedEnum,
            ValueType::UnitEnum => $this->expectCastIfNonStrict(null, $ctx),
        };
    }
}
