<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Unit\Type;

use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Context;
use TypeLang\Mapper\Tests\Unit\Type\Stub\IntBackedEnum;
use TypeLang\Mapper\Tests\Unit\Type\Stub\StringBackedEnum;
use TypeLang\Mapper\Tests\Unit\Type\Stub\UnitEnum;
use TypeLang\Mapper\Type\MixedType;
use TypeLang\Mapper\Type\TypeInterface;

#[Group('type-lang/mapper')]
final class MixedTypeTest extends TypeTestCase
{
    protected function getType(): TypeInterface
    {
        return new MixedType();
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
            ValueType::NegativeExponentNumericString,
            ValueType::Null,
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
            ValueType::NanFloat => $value,
            ValueType::Object,
            ValueType::StringableObject => [],
            ValueType::Array,
            ValueType::EmptyArray => $value,
            ValueType::StringBackedEnum => StringBackedEnum::EXAMPLE->value,
            ValueType::IntBackedEnum => IntBackedEnum::EXAMPLE->value,
            ValueType::UnitEnum => ['name' => UnitEnum::EXAMPLE->name],
        };
    }
}
