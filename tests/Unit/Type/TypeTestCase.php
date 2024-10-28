<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Unit\Type;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Exception\Mapping\RuntimeException;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Runtime\Configuration;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Context\RootContext;
use TypeLang\Mapper\Runtime\Parser\TypeParser;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Parser\TypeParserRuntime;
use TypeLang\Mapper\Runtime\Repository\TypeRepository;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryRuntime;
use TypeLang\Mapper\Tests\Unit\TestCase;
use TypeLang\Mapper\Tests\Unit\Type\Stub\IntBackedEnum;
use TypeLang\Mapper\Tests\Unit\Type\Stub\StringableObject;
use TypeLang\Mapper\Tests\Unit\Type\Stub\StringBackedEnum;
use TypeLang\Mapper\Tests\Unit\Type\Stub\UnitEnum;
use TypeLang\Mapper\Type\TypeInterface;

#[Group('unit'), Group('type-lang/mapper')]
abstract class TypeTestCase extends TestCase
{
    protected readonly PlatformInterface $platform;

    protected readonly TypeRepositoryInterface $types;

    protected readonly TypeParserInterface $parser;

    #[Before]
    protected function setUpDefaultRegistry(): void
    {
        $this->platform = new StandardPlatform();

        $this->parser = new TypeParser(
            runtime: TypeParserRuntime::createFromPlatform(
                platform: $this->platform,
            )
        );

        $this->types = new TypeRepository(
            parser: $this->parser,
            runtime: TypeRepositoryRuntime::createFromPlatform(
                platform: $this->platform,
                parser: $this->parser,
            ),
        );
    }

    abstract protected function getType(): TypeInterface;

    abstract protected function getCastExpectation(mixed $value, ValueType $type, Context $ctx): mixed;

    protected function getNormalizationExpectation(mixed $value, ValueType $type, Context $ctx): mixed
    {
        return $this->getCastExpectation($value, $type, $ctx);
    }

    protected function getDenormalizationExpectation(mixed $value, ValueType $type, Context $ctx): mixed
    {
        return $this->getCastExpectation($value, $type, $ctx);
    }

    protected function expectCastIfNonStrict(mixed $expected, Context $ctx): mixed
    {
        $this->expectException(RuntimeException::class);

        return "<\0MUST_THROW_ERROR(" . __FUNCTION__ . ")\0>";
    }

    protected function expectMappingError(): mixed
    {
        $this->expectException(RuntimeException::class);

        return "<\0MUST_THROW_ERROR(" . __FUNCTION__ . ")\0>";
    }

    protected function expectTypeNotFoundError(): mixed
    {
        $this->expectException(TypeNotFoundException::class);

        return "<\0MUST_THROW_ERROR(" . __FUNCTION__ . ")\0>";
    }

    public static function configDataProvider(): iterable
    {
        yield 'default' => [new Configuration()];
    }

    public static function valuesDataProvider(): iterable
    {
        foreach (self::configDataProvider() as $name => [$config]) {
            $suffix = "with $name config";

            yield "string $suffix" => ['EXAMPLE', ValueType::String, $config];
            yield "int numeric string $suffix" => ['42', ValueType::IntNumericString, $config];
            yield "negative int numeric string $suffix" => ['-42', ValueType::NegativeIntNumericString, $config];
            yield "float numeric string $suffix" => ['3232.42', ValueType::FloatNumericString, $config];
            yield "negative float numeric string $suffix" => ['-3232.42', ValueType::NegativeFloatNumericString, $config];
            yield "exponent numeric string $suffix" => ['100e10', ValueType::ExponentNumericString, $config];
            yield "negative exponent numeric string $suffix" => ['-100e10', ValueType::NegativeExponentNumericString, $config];
            yield "null $suffix" => [null, ValueType::Null, $config];
            yield "int $suffix" => [0xDEAD_BEEF, ValueType::Int, $config];
            yield "negative int $suffix" => [-0xDEAD_BEEF, ValueType::NegativeInt, $config];
            yield "true $suffix" => [true, ValueType::True, $config];
            yield "false $suffix" => [false, ValueType::False, $config];
            yield "float $suffix" => [42.0, ValueType::Float, $config];
            yield "around zero float $suffix" => [.1, ValueType::AroundZeroFloat, $config];
            yield "around one float $suffix" => [.9, ValueType::AroundOneFloat, $config];
            yield "exponent float $suffix" => [1e100, ValueType::ExponentFloat, $config];
            yield "inf float $suffix" => [\INF, ValueType::InfFloat, $config];
            yield "negative inf float $suffix" => [-\INF, ValueType::NegativeInfFloat, $config];
            yield "nan float $suffix" => [\NAN, ValueType::NanFloat, $config];
            yield "object $suffix" => [new \stdClass(), ValueType::Object, $config];
            yield "stringable object $suffix" => [new StringableObject(), ValueType::StringableObject, $config];
            yield "array $suffix" => [[1, 2, 3], ValueType::Array, $config];
            yield "empty array $suffix" => [[], ValueType::EmptyArray, $config];
            yield "string backed enum $suffix" => [StringBackedEnum::EXAMPLE, ValueType::StringBackedEnum, $config];
            yield "int backed enum $suffix" => [IntBackedEnum::EXAMPLE, ValueType::IntBackedEnum, $config];
            yield "unit enum $suffix" => [UnitEnum::EXAMPLE, ValueType::UnitEnum, $config];
        }
    }

    #[DataProvider('valuesDataProvider')]
    public function testNormalization(mixed $value, ValueType $type, Configuration $config): void
    {
        $local = RootContext::forNormalization(
            value: $value,
            config: $config,
            parser: $this->parser,
            types: $this->types,
        );

        $expected = $this->getNormalizationExpectation($value, $type, $local);

        $actual = $this->normalize($value, $local);

        $this->assertCasting($value, $expected, $actual);
    }

    #[DataProvider('valuesDataProvider')]
    public function testDenormalization(mixed $value, ValueType $type, Configuration $config): void
    {
        $local = RootContext::forDenormalization(
            value: $value,
            config: $config,
            parser: $this->parser,
            types: $this->types,
        );

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

    protected function normalize(mixed $value, Context $context): mixed
    {
        $type = $this->getType();

        return $type->cast($value, $context);
    }

    protected function denormalize(mixed $value, Context $context): mixed
    {
        $type = $this->getType();

        return $type->cast($value, $context);
    }
}
