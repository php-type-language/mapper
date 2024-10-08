<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Unit\Type;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Exception\Mapping\MappingException;
use TypeLang\Mapper\Tests\Unit\TestCase;
use TypeLang\Mapper\Tests\Unit\Type\Stub\IntBackedEnum;
use TypeLang\Mapper\Tests\Unit\Type\Stub\StringableObject;
use TypeLang\Mapper\Tests\Unit\Type\Stub\StringBackedEnum;
use TypeLang\Mapper\Tests\Unit\Type\Stub\UnitEnum;
use TypeLang\Mapper\Type\Context\Context;
use TypeLang\Mapper\Type\Context\Direction;
use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Mapper\Type\Repository\Repository;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Mapper\Type\TypeInterface;

#[Group('unit'), Group('type-lang/mapper')]
abstract class TypeTestCase extends TestCase
{
    protected readonly RepositoryInterface $types;

    #[Before]
    protected function setUpDefaultRegistry(): void
    {
        $this->types = new Repository();
    }

    abstract protected function getType(): TypeInterface;

    abstract protected function getCastExpectation(mixed $value, ValueType $type, LocalContext $ctx): mixed;

    protected function getNormalizationExpectation(mixed $value, ValueType $type, LocalContext $ctx): mixed
    {
        return $this->getCastExpectation($value, $type, $ctx);
    }

    protected function getDenormalizationExpectation(mixed $value, ValueType $type, LocalContext $ctx): mixed
    {
        return $this->getCastExpectation($value, $type, $ctx);
    }

    protected function expectCastIfNonStrict(mixed $expected, Context $ctx): mixed
    {
        if ($ctx->isStrictTypesEnabled()) {
            $this->expectException(MappingException::class);

            return "<\0MUST_THROW_ERROR(" . __FUNCTION__ . ")\0>";
        }

        return $expected;
    }

    protected function expectMappingError(): mixed
    {
        $this->expectException(MappingException::class);

        return "<\0MUST_THROW_ERROR(" . __FUNCTION__ . ")\0>";
    }

    protected function expectTypeNotFoundError(): mixed
    {
        $this->expectException(TypeNotFoundException::class);

        return "<\0MUST_THROW_ERROR(" . __FUNCTION__ . ")\0>";
    }

    public static function contextDataProvider(): iterable
    {
        yield 'non-strict' => [new Context(strictTypes: false)];
        yield 'strict' => [new Context(strictTypes: true)];
        yield 'default' => [new Context()];
    }

    public static function valuesDataProvider(): iterable
    {
        foreach (self::contextDataProvider() as $name => [$context]) {
            $suffix = "with $name context";

            yield "string $suffix" => ['EXAMPLE', ValueType::String, $context];
            yield "int numeric string $suffix" => ['42', ValueType::IntNumericString, $context];
            yield "negative int numeric string $suffix" => ['-42', ValueType::NegativeIntNumericString, $context];
            yield "float numeric string $suffix" => ['3232.42', ValueType::FloatNumericString, $context];
            yield "negative float numeric string $suffix" => ['-3232.42', ValueType::NegativeFloatNumericString, $context];
            yield "exponent numeric string $suffix" => ['100e10', ValueType::ExponentNumericString, $context];
            yield "negative exponent numeric string $suffix" => ['-100e10', ValueType::NegativeExponentNumericString, $context];
            yield "null $suffix" => [null, ValueType::Null, $context];
            yield "int $suffix" => [0xDEAD_BEEF, ValueType::Int, $context];
            yield "negative int $suffix" => [-0xDEAD_BEEF, ValueType::NegativeInt, $context];
            yield "true $suffix" => [true, ValueType::True, $context];
            yield "false $suffix" => [false, ValueType::False, $context];
            yield "float $suffix" => [42.0, ValueType::Float, $context];
            yield "around zero float $suffix" => [.1, ValueType::AroundZeroFloat, $context];
            yield "around one float $suffix" => [.9, ValueType::AroundOneFloat, $context];
            yield "exponent float $suffix" => [1e100, ValueType::ExponentFloat, $context];
            yield "inf float $suffix" => [\INF, ValueType::InfFloat, $context];
            yield "negative inf float $suffix" => [-\INF, ValueType::NegativeInfFloat, $context];
            yield "nan float $suffix" => [\NAN, ValueType::NanFloat, $context];
            yield "object $suffix" => [new \stdClass(), ValueType::Object, $context];
            yield "stringable object $suffix" => [new StringableObject(), ValueType::StringableObject, $context];
            yield "array $suffix" => [[1, 2, 3], ValueType::Array, $context];
            yield "empty array $suffix" => [[], ValueType::EmptyArray, $context];
            yield "string backed enum $suffix" => [StringBackedEnum::EXAMPLE, ValueType::StringBackedEnum, $context];
            yield "int backed enum $suffix" => [IntBackedEnum::EXAMPLE, ValueType::IntBackedEnum, $context];
            yield "unit enum $suffix" => [UnitEnum::EXAMPLE, ValueType::UnitEnum, $context];
        }
    }

    #[DataProvider('valuesDataProvider')]
    public function testNormalization(mixed $value, ValueType $type, Context $ctx): void
    {
        $local = LocalContext::fromContext(Direction::Normalize, $this->types, $ctx);

        $expected = $this->getNormalizationExpectation($value, $type, $local);

        $actual = $this->normalize($value, $local);

        $this->assertCasting($value, $expected, $actual);
    }

    #[DataProvider('valuesDataProvider')]
    public function testDenormalization(mixed $value, ValueType $type, Context $ctx): void
    {
        $local = LocalContext::fromContext(Direction::Denormalize, $this->types, $ctx);

        $expected = $this->getDenormalizationExpectation($value, $type, $local);

        $actual = $this->denormalize($value, $local);

        $this->assertCasting($value, $expected, $actual);
    }

    private function assertCasting(mixed $value, mixed $expected, mixed $actual): void
    {
        $message = \vsprintf('Passed value %s was converted to %s, but expected is %s', [
            \var_export($value, true),
            \var_export($actual, true),
            \var_export($expected, true),
        ]);

        if (\is_float($expected) && \is_nan($expected)) {
            self::assertNan($actual, $message);
        } elseif (\is_object($expected)) {
            self::assertEquals($expected, $actual, $message);
        } else {
            self::assertSame($expected, $actual, $message);
        }
    }

    protected function normalize(mixed $value, LocalContext $context): mixed
    {
        $type = $this->getType();

        return $type->cast($value, $context);
    }

    protected function denormalize(mixed $value, LocalContext $context): mixed
    {
        $type = $this->getType();

        return $type->cast($value, $context);
    }
}
