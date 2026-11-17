<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Coercer\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Coercer\FloatCoercer;
use TypeLang\Mapper\Coercer\StringCoercer;

final class StringCoercerTest extends TestCase
{
    protected static function createCoercer(): StringCoercer
    {
        return new StringCoercer();
    }

    /**
     * @return iterable<non-empty-string, array{mixed, string}>
     */
    public static function coercibleValueDataProvider(): iterable
    {
        yield 'string' => ['abc', 'abc'];
        yield 'empty string' => ['', ''];
        yield 'string with surrounding spaces' => [' 42 ', ' 42 '];
        yield 'string with trailing letters' => ['42abc', '42abc'];
        yield 'long string' => [\str_repeat('a', 10000), \str_repeat('a', 10000)];

        yield 'int' => [42, '42'];
        yield 'zero int' => [0, '0'];
        yield 'negative int' => [-42, '-42'];
        yield 'max int' => [\PHP_INT_MAX, '9223372036854775807'];
        yield 'min int' => [\PHP_INT_MIN, '-9223372036854775808'];

        yield 'float' => [1.5, '1.5'];
        yield 'negative float' => [-1.5, '-1.5'];
        yield 'integral float' => [42.0, '42'];
        yield 'zero float' => [0.0, '0'];
        yield 'negative zero float' => [-0.0, '-0'];
        yield 'inexact float sum' => [0.1 + 0.2, '0.30000000000000004'];
        yield 'large integral float' => [1e15, '1000000000000000'];
        yield 'larger integral float' => [1e16, '10000000000000000'];
        yield 'huge float' => [1e25, '1.0e+25'];
        yield 'int overflow float' => [\PHP_INT_MAX + 1, '9.223372036854776e+18'];
        yield 'small float' => [0.0001, '0.0001'];
        yield 'tiny float' => [1e-7, '1.0e-7'];
        yield 'max float' => [\PHP_FLOAT_MAX, '1.7976931348623157e+308'];
        yield 'min subnormal float' => [5e-324, '5.0e-324'];
    }

    /**
     * @return iterable<non-empty-string, array{mixed}>
     */
    public static function rejectedValueDataProvider(): iterable
    {
        yield 'infinity' => [\INF];
        yield 'negative infinity' => [-\INF];
        yield 'not a number' => [\NAN];
        yield 'true' => [true];
        yield 'false' => [false];
        yield 'empty array' => [[]];
        yield 'array' => [['a']];
        yield 'object' => [new \stdClass()];
    }

    /**
     * Finite floats under each `serialize_precision` ini value
     *
     * @return iterable<non-empty-string, array{non-empty-string, float}>
     */
    public static function finiteFloatDataProvider(): iterable
    {
        $floats = [
            'float' => 1.5,
            'inexact float' => 0.1,
            'inexact float sum' => 0.1 + 0.2,
            'negative zero float' => -0.0,
            'huge float' => 1e25,
            'int overflow float' => \PHP_INT_MAX + 1,
            'max float' => \PHP_FLOAT_MAX,
            'min subnormal float' => 5e-324,
            'one third' => 1 / 3,
        ];

        foreach (['-1', '17', '5', '0'] as $precision) {
            foreach ($floats as $name => $float) {
                yield \sprintf('%s with precision %s', $name, $precision) => [$precision, $float];
            }
        }
    }

    /**
     * @param non-empty-string $precision
     */
    #[DataProvider('finiteFloatDataProvider')]
    public function testFloatNotationIsReadBackToSameFloat(string $precision, float $value): void
    {
        $notation = self::coerceWithPrecision($precision, $value);

        self::assertIsString($notation);
        self::assertSameValue($value, (new FloatCoercer())->coerce($notation));
    }

    public function testFloatNotationKeepsAllDigitsWithLowPrecision(): void
    {
        self::assertSame('0.30000000000000004', self::coerceWithPrecision('5', 0.1 + 0.2));
    }

    public function testFloatNotationIsShortestWithHighPrecision(): void
    {
        self::assertSame('0.1', self::coerceWithPrecision('17', 0.1));
    }

    public function testFloatNotationRestoresPrecisionSetting(): void
    {
        $previous = \ini_set('serialize_precision', '17');

        self::assertIsString($previous);

        try {
            (new StringCoercer())->coerce(0.1);

            self::assertSame('17', \ini_get('serialize_precision'));
        } finally {
            \ini_set('serialize_precision', $previous);
        }
    }

    /**
     * @param non-empty-string $precision
     */
    private static function coerceWithPrecision(string $precision, float $value): ?string
    {
        $previous = \ini_set('serialize_precision', $precision);

        self::assertIsString($previous);

        try {
            return (new StringCoercer())->coerce($value);
        } finally {
            \ini_set('serialize_precision', $previous);
        }
    }
}
