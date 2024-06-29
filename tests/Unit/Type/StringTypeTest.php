<?php

declare(strict_types=1);

namespace Serafim\Mapper\Tests\Unit\Type;

use Serafim\Mapper\Context;
use Serafim\Mapper\Type\StringType;
use Serafim\Mapper\Type\TypeInterface;

final class StringTypeTest extends TypeTestCase
{
    protected function getType(): TypeInterface
    {
        return new StringType();
    }

    protected function getNormalizationExpectation(mixed $value, ValueType $type, Context $ctx): mixed
    {
        return match ($type) {
            ValueType::String => 'EXAMPLE',
            ValueType::IntNumericString => '42',
            ValueType::NegativeIntNumericString => '-42',
            ValueType::FloatNumericString => '3232.42',
            ValueType::NegativeFloatNumericString => '-3232.42',
            ValueType::ExponentNumericString => '100e10',
            ValueType::NegativeExponentNumericString => '-100e10',
            ValueType::Null => $this->expectCastIfNonStrict('', $ctx),
            ValueType::Int => $this->expectCastIfNonStrict('3735928559', $ctx),
            ValueType::NegativeInt => $this->expectCastIfNonStrict('-3735928559', $ctx),
            ValueType::True => $this->expectCastIfNonStrict('1', $ctx),
            ValueType::False => $this->expectCastIfNonStrict('0', $ctx),
            ValueType::Float => $this->expectCastIfNonStrict('42', $ctx),
            ValueType::AroundZeroFloat => $this->expectCastIfNonStrict('0.1', $ctx),
            ValueType::AroundOneFloat => $this->expectCastIfNonStrict('0.9', $ctx),
            ValueType::ExponentFloat => $this->expectCastIfNonStrict('1.0E+100', $ctx),
            ValueType::InfFloat => $this->expectCastIfNonStrict('INF', $ctx),
            ValueType::NegativeInfFloat => $this->expectCastIfNonStrict('-INF', $ctx),
            ValueType::NanFloat => $this->expectCastIfNonStrict('NAN', $ctx),
            ValueType::Object => $this->expectMappingError(),
            ValueType::StringableObject => $this->expectCastIfNonStrict('<EXAMPLE>', $ctx),
            ValueType::Array => $this->expectMappingError(),
            ValueType::EmptyArray => $this->expectMappingError(),
            ValueType::StringBackedEnum => $this->expectCastIfNonStrict('example', $ctx),
            ValueType::IntBackedEnum => $this->expectCastIfNonStrict('3735928559', $ctx),
            ValueType::UnitEnum => $this->expectMappingError(),
        };
    }
}
