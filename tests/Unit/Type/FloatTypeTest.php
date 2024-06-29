<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Unit\Type;

use TypeLang\Mapper\Context;
use TypeLang\Mapper\Type\FloatType;
use TypeLang\Mapper\Type\TypeInterface;

final class FloatTypeTest extends TypeTestCase
{
    protected function getType(): TypeInterface
    {
        return new FloatType();
    }

    protected function getNormalizationExpectation(mixed $value, ValueType $type, Context $ctx): mixed
    {
        return match ($type) {
            ValueType::String => $this->expectCastIfNonStrict(1.0, $ctx),
            ValueType::IntNumericString => $this->expectCastIfNonStrict(42.0, $ctx),
            ValueType::NegativeIntNumericString => $this->expectCastIfNonStrict(-42.0, $ctx),
            ValueType::FloatNumericString => $this->expectCastIfNonStrict(3232.42, $ctx),
            ValueType::NegativeFloatNumericString => $this->expectCastIfNonStrict(-3232.42, $ctx),
            ValueType::ExponentNumericString => $this->expectCastIfNonStrict(100e10, $ctx),
            ValueType::NegativeExponentNumericString => $this->expectCastIfNonStrict(-100e10, $ctx),
            ValueType::Null => $this->expectCastIfNonStrict(0.0, $ctx),
            ValueType::Int => 3735928559.0,
            ValueType::NegativeInt => -3735928559.0,
            ValueType::True => $this->expectCastIfNonStrict(1.0, $ctx),
            ValueType::False => $this->expectCastIfNonStrict(0.0, $ctx),
            ValueType::Float => 42.0,
            ValueType::AroundZeroFloat => 0.1,
            ValueType::AroundOneFloat => 0.9,
            ValueType::ExponentFloat => 1e100,
            ValueType::InfFloat => \INF,
            ValueType::NegativeInfFloat => -\INF,
            ValueType::NanFloat => \NAN,
            ValueType::Object => $this->expectMappingError(),
            ValueType::StringableObject => $this->expectMappingError(),
            ValueType::Array => $this->expectMappingError(),
            ValueType::EmptyArray => $this->expectMappingError(),
            ValueType::StringBackedEnum => $this->expectCastIfNonStrict(1.0, $ctx),
            ValueType::IntBackedEnum => $this->expectCastIfNonStrict(3735928559.0, $ctx),
            ValueType::UnitEnum => $this->expectMappingError(),
        };
    }
}
