<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Unit\Type;

use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Tests\Unit\Type\Stub\IntBackedEnum;
use TypeLang\Mapper\Tests\Unit\Type\Stub\StringableObject;
use TypeLang\Mapper\Tests\Unit\Type\Stub\StringBackedEnum;
use TypeLang\Mapper\Type\MixedType;
use TypeLang\Mapper\Type\TypeInterface;

#[Group('type-lang/mapper')]
final class MixedTypeTest extends TypeTestCase
{
    protected function getType(): TypeInterface
    {
        return new MixedType();
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
            ValueType::Object => $ctx->isNormalization() ? [] : (object)[],
            ValueType::StringableObject => $ctx->isNormalization() ? [] : new StringableObject(),
            ValueType::Array,
            ValueType::EmptyArray => $value,
            ValueType::StringBackedEnum => $ctx->isNormalization()
                ? StringBackedEnum::EXAMPLE->value
                : $this->expectMappingError(),
            ValueType::IntBackedEnum => $ctx->isNormalization()
                ? IntBackedEnum::EXAMPLE->value
                : $this->expectMappingError(),
            ValueType::UnitEnum => $this->expectTypeNotFoundError(),
        };
    }
}
