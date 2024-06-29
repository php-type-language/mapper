<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Unit\Type;

use TypeLang\Mapper\Context;
use TypeLang\Mapper\Type\IntType;
use TypeLang\Mapper\Type\TypeInterface;

final class IntTypeTest extends TypeTestCase
{
    protected function getType(): TypeInterface
    {
        return new IntType();
    }

    protected function getNormalizationExpectation(mixed $value, ValueType $type, Context $ctx): mixed
    {
        return match ($type) {
            ValueType::String => $this->expectCastIfNonStrict(1, $ctx),
            ValueType::IntNumericString => $this->expectCastIfNonStrict(42, $ctx),
            ValueType::NegativeIntNumericString => $this->expectCastIfNonStrict(-42, $ctx),
            ValueType::FloatNumericString => $this->expectCastIfNonStrict(1, $ctx),
            ValueType::NegativeFloatNumericString => $this->expectCastIfNonStrict(1, $ctx),
            ValueType::ExponentNumericString => $this->expectCastIfNonStrict(1, $ctx),
            ValueType::NegativeExponentNumericString => $this->expectCastIfNonStrict(1, $ctx),
            ValueType::Null => $this->expectCastIfNonStrict(0, $ctx),
            ValueType::Int => 0xDEAD_BEEF,
            ValueType::NegativeInt => -0xDEAD_BEEF,
            ValueType::True => $this->expectCastIfNonStrict(1, $ctx),
            ValueType::False => $this->expectCastIfNonStrict(0, $ctx),
            ValueType::Float => $this->expectCastIfNonStrict(42, $ctx),
            ValueType::AroundZeroFloat => $this->expectCastIfNonStrict(0, $ctx),
            ValueType::AroundOneFloat => $this->expectCastIfNonStrict(0, $ctx),
            ValueType::ExponentFloat => $this->expectCastIfNonStrict(0, $ctx),
            ValueType::InfFloat => $this->expectCastIfNonStrict(\PHP_INT_MAX, $ctx),
            ValueType::NegativeInfFloat => $this->expectCastIfNonStrict(\PHP_INT_MIN, $ctx),
            ValueType::NanFloat => $this->expectCastIfNonStrict(0, $ctx),
            ValueType::Object => $this->expectMappingError(),
            ValueType::StringableObject => $this->expectMappingError(),
            ValueType::Array => $this->expectMappingError(),
            ValueType::EmptyArray => $this->expectMappingError(),
            ValueType::StringBackedEnum => $this->expectCastIfNonStrict(1, $ctx),
            ValueType::IntBackedEnum => $this->expectCastIfNonStrict(0xDEAD_BEEF, $ctx),
            ValueType::UnitEnum => $this->expectMappingError(),
        };
    }
}
