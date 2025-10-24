<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Context\RootContext;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Configuration;
use TypeLang\Mapper\Runtime\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Runtime\Value\JsonLikeValuePrinter;
use TypeLang\Mapper\Tests\TestCase;
use TypeLang\Mapper\Tests\Type\Stub\IntBackedEnumStub;
use TypeLang\Mapper\Tests\Type\Stub\StringBackedEnumStub;
use TypeLang\Mapper\Tests\Type\Stub\UnitEnumStub;
use TypeLang\Mapper\Type\TypeInterface;

abstract class TypeTestCase extends TestCase
{
    private static int $i = 0;

    /**
     * @return iterable<mixed, bool>
     */
    final protected static function defaultMatchDataProviderSamples(): iterable
    {
        foreach (self::defaultDataProviderSamples() as $value) {
            yield $value => false;
        }
    }

    /**
     * @return iterable<mixed, \Throwable>
     */
    final protected static function defaultCastDataProviderSamples(): iterable
    {
        $printer = new JsonLikeValuePrinter();

        foreach (self::defaultDataProviderSamples() as $value) {
            yield $value => new \ValueError(\sprintf(
                'Passed value %s is invalid',
                $printer->print($value),
            ));
        }
    }

    /**
     * @return iterable<array-key, mixed>
     */
    private static function defaultDataProviderSamples(): iterable
    {
        // Integer values
        yield \PHP_INT_MAX + 1;
        yield \PHP_INT_MAX;
        yield 42;
        yield 1;
        yield 0;
        yield -1;
        yield -42;
        yield \PHP_INT_MIN;
        yield \PHP_INT_MIN - 1;

        // Numeric integer-like string values
        yield "9223372036854775808";
        yield "9223372036854775807";
        yield "42";
        yield "1";
        yield "0";
        yield "-1";
        yield "-42";
        yield "-9223372036854775808";
        yield "-9223372036854775809";

        // Float values
        yield 9223372036854775808.0;
        yield 9223372036854775807.0;
        yield 42.5;
        yield 42.0;
        yield 1.0;
        yield 0.0;
        yield -1.0;
        yield -42.0;
        yield -42.5;
        yield -9223372036854775808.0;
        yield -9223372036854775809.0;

        yield INF;
        yield -INF;
        yield NAN;

        // Numeric float-like string values
        yield "9223372036854775808.0";
        yield "9223372036854775807.0";
        yield "42.5";
        yield "42.0";
        yield "1.0";
        yield "0.0";
        yield "-1.0";
        yield "-42.0";
        yield "-42.5";
        yield "-9223372036854775808.0";
        yield "-9223372036854775809.0";

        // Null
        yield null;

        // Boolean
        yield true;
        yield false;

        // Boolean-like strings
        yield "true";
        yield "false";

        // Strings
        yield "non empty";
        yield "";

        // Array values
        yield [];
        yield [0 => 23];
        yield ['key' => 42];

        // Object values
        yield (object)[];
        yield (object)['key' => 'val'];
        yield (object)['val'];

        // Resource
        yield \fopen('php://memory', 'rb');
        \fclose($stream = \fopen('php://memory', 'rb'));
        yield $stream; // closed resource

        // Enum values
        yield UnitEnumStub::ExampleCase;
        // This behavior can be confusing to the user, since the "public"
        // type (i.e., the one displayed to the user) for an enum is an int,
        // but the actual type is an enum's object.
        //
        // Thus, the error displays "expected an int, but received an int,"
        // which is very bad.
        yield IntBackedEnumStub::ExampleCase;
        yield StringBackedEnumStub::ExampleCase;
    }

    protected static function assertIfNotException(mixed $expected, mixed $actual): void
    {
        switch (true) {
            case $expected instanceof \Throwable:
                break;
            case \is_array($expected):
            case \is_object($expected):
                self::assertEquals($expected, $actual);
                break;
            case \is_float($expected) && \is_nan($expected):
                self::assertNan($actual);
                break;
            default:
                self::assertSame($expected, $actual);
        }
    }

    protected function expectTypeErrorIfException(mixed $expected): void
    {
        if (!$expected instanceof \Throwable) {
            return;
        }

        $this->expectExceptionMessage($expected->getMessage());
        $this->expectException(InvalidValueException::class);
    }

    /**
     * @return non-empty-string
     */
    private static function dataProviderKeyOf(mixed $value): string
    {
        return \vsprintf('%s(%s)#%d', [
            \get_debug_type($value),
            \is_array($value) || \is_object($value)
                ? \json_encode($value)
                : \var_export($value, true),
            ++self::$i,
        ]);
    }

    private static function dataProviderOf(iterable $data): iterable
    {
        foreach ($data as $value => $expected) {
            yield self::dataProviderKeyOf($value) => [$value, $expected];
        }
    }

    /**
     * @return iterable<non-empty-string, array{mixed, bool}>
     */
    public static function matchStrictNormalizationDataProvider(): iterable
    {
        return self::dataProviderOf(static::matchNormalizationValues(true));
    }

    #[DataProvider('matchStrictNormalizationDataProvider')]
    public function testMatchStrictNormalization(mixed $value, bool $expected): void
    {
        $type = static::createType();

        $actual = $type->match($value, $this->createNormalizationContext(
            value: $value,
            strictTypes: true,
        ));

        self::assertSame($expected, $actual);
    }

    /**
     * @return iterable<non-empty-string, array{mixed, bool}>
     */
    public static function matchNonStrictNormalizationDataProvider(): iterable
    {
        return self::dataProviderOf(static::matchNormalizationValues(false));
    }

    #[DataProvider('matchNonStrictNormalizationDataProvider')]
    public function testMatchNonStrictNormalization(mixed $value, bool $expected): void
    {
        $type = static::createType();

        $actual = $type->match($value, $this->createNormalizationContext(
            value: $value,
            strictTypes: false,
        ));

        self::assertSame($expected, $actual);
    }

    /**
     * @return iterable<non-empty-string, array{mixed, bool}>
     */
    public static function matchStrictDenormalizationDataProvider(): iterable
    {
        return self::dataProviderOf(static::matchDenormalizationValues(true));
    }

    #[DataProvider('matchStrictDenormalizationDataProvider')]
    public function testMatchStrictDenormalization(mixed $value, bool $expected): void
    {
        $type = static::createType();

        $actual = $type->match($value, $this->createDenormalizationContext(
            value: $value,
            strictTypes: true,
        ));

        self::assertSame($expected, $actual);
    }

    /**
     * @return iterable<non-empty-string, array{mixed, bool}>
     */
    public static function matchNonStrictDenormalizationDataProvider(): iterable
    {
        return self::dataProviderOf(static::matchDenormalizationValues(false));
    }

    #[DataProvider('matchStrictDenormalizationDataProvider')]
    public function testMatchNonStrictDenormalization(mixed $value, bool $expected): void
    {
        $type = static::createType();

        $actual = $type->match($value, $this->createDenormalizationContext(
            value: $value,
            strictTypes: false,
        ));

        self::assertSame($expected, $actual);
    }

    /**
     * @return iterable<non-empty-string, array{mixed, mixed|\Throwable}>
     */
    public static function castStrictNormalizationDataProvider(): iterable
    {
        return self::dataProviderOf(static::castNormalizationValues(true));
    }

    #[DataProvider('castStrictNormalizationDataProvider')]
    public function testCastStrictNormalization(mixed $value, mixed $expected): void
    {
        $this->expectTypeErrorIfException($expected);

        $type = static::createType();

        $actual = $type->cast($value, $this->createNormalizationContext(
            value: $value,
            strictTypes: true,
        ));

        self::assertIfNotException($expected, $actual);
    }

    /**
     * @return iterable<non-empty-string, array{mixed, mixed|\Throwable}>
     */
    public static function castNonStrictNormalizationDataProvider(): iterable
    {
        return self::dataProviderOf(static::castNormalizationValues(false));
    }

    #[DataProvider('castNonStrictNormalizationDataProvider')]
    public function testCastNonStrictNormalization(mixed $value, mixed $expected): void
    {
        $this->expectTypeErrorIfException($expected);

        $type = static::createType();

        $actual = $type->cast($value, $this->createNormalizationContext(
            value: $value,
            strictTypes: false,
        ));

        self::assertIfNotException($expected, $actual);
    }

    /**
     * @return iterable<non-empty-string, array{mixed, mixed|\Throwable}>
     */
    public static function castStrictDenormalizationDataProvider(): iterable
    {
        return self::dataProviderOf(static::castDenormalizationValues(true));
    }

    #[DataProvider('castStrictDenormalizationDataProvider')]
    public function testCastStrictDenormalization(mixed $value, mixed $expected): void
    {
        $this->expectTypeErrorIfException($expected);

        $type = static::createType();

        $actual = $type->cast($value, $this->createDenormalizationContext(
            value: $value,
            strictTypes: true,
        ));

        self::assertIfNotException($expected, $actual);
    }

    /**
     * @return iterable<non-empty-string, array{mixed, mixed|\Throwable}>
     */
    public static function castNonStrictDenormalizationDataProvider(): iterable
    {
        return self::dataProviderOf(static::castDenormalizationValues(false));
    }

    #[DataProvider('castNonStrictDenormalizationDataProvider')]
    public function testCastNonStrictDenormalization(mixed $value, mixed $expected): void
    {
        $this->expectTypeErrorIfException($expected);

        $type = static::createType();

        $actual = $type->cast($value, $this->createDenormalizationContext(
            value: $value,
            strictTypes: false,
        ));

        self::assertIfNotException($expected, $actual);
    }

    /**
     * @return iterable<mixed, bool>
     */
    abstract protected static function matchNormalizationValues(bool $strict): iterable;

    /**
     * @return iterable<mixed, bool>
     */
    abstract protected static function matchDenormalizationValues(bool $strict): iterable;

    /**
     * @return iterable<mixed, bool>
     */
    abstract protected static function castNormalizationValues(bool $strict): iterable;

    /**
     * @return iterable<mixed, bool>
     */
    abstract protected static function castDenormalizationValues(bool $strict): iterable;

    /**
     * @return TypeInterface<mixed>
     */
    abstract protected static function createType(): TypeInterface;

    protected function createDenormalizationContext(mixed $value, bool $strictTypes): RootContext
    {
        $config = new Configuration(isStrictTypes: $strictTypes);

        return RootContext::forDenormalization(
            value: $value,
            config: $config,
            extractor: $this->createMock(TypeExtractorInterface::class),
            parser: $this->createMock(TypeParserInterface::class),
            types: $this->createMock(TypeRepositoryInterface::class),
        );
    }

    protected function createNormalizationContext(mixed $value, bool $strictTypes): RootContext
    {
        $config = new Configuration(isStrictTypes: $strictTypes);

        return RootContext::forNormalization(
            value: $value,
            config: $config,
            extractor: $this->createMock(TypeExtractorInterface::class),
            parser: $this->createMock(TypeParserInterface::class),
            types: $this->createMock(TypeRepositoryInterface::class),
        );
    }
}
