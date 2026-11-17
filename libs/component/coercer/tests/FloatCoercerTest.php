<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Coercer\Tests;

use TypeLang\Mapper\Coercer\FloatCoercer;

final class FloatCoercerTest extends TestCase
{
    protected static function createCoercer(): FloatCoercer
    {
        return new FloatCoercer();
    }

    /**
     * @return iterable<non-empty-string, array{mixed, float}>
     */
    public static function coercibleValueDataProvider(): iterable
    {
        yield 'float' => [1.5, 1.5];
        yield 'zero float' => [0.0, 0.0];
        yield 'negative zero float' => [-0.0, -0.0];
        yield 'max float' => [\PHP_FLOAT_MAX, \PHP_FLOAT_MAX];
        yield 'infinity' => [\INF, \INF];
        yield 'negative infinity' => [-\INF, -\INF];
        yield 'not a number' => [\NAN, \NAN];

        yield 'int' => [42, 42.0];
        yield 'zero int' => [0, 0.0];
        yield 'negative int' => [-42, -42.0];
        yield 'max exact int' => [2 ** 53, 9007199254740992.0];
        yield 'min exact int' => [-(2 ** 53), -9007199254740992.0];
        yield 'large power of two int' => [2 ** 62, 4611686018427387904.0];
        yield 'largest exact int below max int' => [9223372036854774784, 9223372036854774784.0];
        yield 'min int' => [\PHP_INT_MIN, -9223372036854775808.0];

        yield 'decimal string' => ['1.5', 1.5];
        yield 'decimal string with plus sign' => ['+1.5', 1.5];
        yield 'negative decimal string' => ['-1.5', -1.5];
        yield 'integer string' => ['42', 42.0];
        yield 'zero string' => ['0', 0.0];
        yield 'negative zero string' => ['-0', -0.0];
        yield 'negative zero decimal string' => ['-0.0', -0.0];
        yield 'string with leading zeros' => ['007.50', 7.5];
        yield 'exponent string' => ['1e3', 1000.0];
        yield 'uppercase exponent string' => ['1E3', 1000.0];
        yield 'exponent string with plus sign' => ['1e+3', 1000.0];
        yield 'negative exponent string' => ['1e-3', 0.001];
        yield 'decimal string with leading dot' => ['.5', 0.5];
        yield 'negative decimal string with leading dot' => ['-.5', -0.5];
        yield 'decimal string with trailing dot' => ['5.', 5.0];
        yield 'inexact decimal string' => ['0.1', 0.1];
        yield 'string above max int' => ['9223372036854775808', 9223372036854775808.0];
        yield 'exact integer string above float precision' => ['9007199254740994', 9007199254740994.0];
        yield 'decimal string above float precision' => ['9007199254740993.0', 9007199254740992.0];
        yield 'long integer string with leading zeros' => ['0000000000000000042', 42.0];
        yield 'long decimal string' => ['0.' . \str_repeat('3', 1000), 0.3333333333333333];
        yield 'max float string' => ['1.7976931348623157e308', \PHP_FLOAT_MAX];
        yield 'min subnormal float string' => ['4.9e-324', 5.0E-324];
        yield 'zero decimal string' => ['0.0', 0.0];
        yield 'zero exponent string' => ['0e5', 0.0];
        yield 'negative zero exponent string' => ['-0.0e-999', -0.0];
        yield 'zeros string with exponent' => ['000.000e+999', 0.0];
        yield 'subnormal float string' => ['5e-324', 5.0E-324];
        yield 'negative subnormal float string' => ['-5e-324', -5.0E-324];
    }

    /**
     * @return iterable<non-empty-string, array{mixed}>
     */
    public static function rejectedValueDataProvider(): iterable
    {
        yield 'int above float precision' => [2 ** 53 + 1];
        yield 'int below float precision' => [-(2 ** 53) - 1];
        yield 'max int' => [\PHP_INT_MAX];
        yield 'int rounded to int overflow' => [9223372036854775000];

        yield 'integer string above float precision' => ['9007199254740993'];
        yield 'negative integer string below float precision' => ['-9007199254740993'];
        yield 'max int string' => ['9223372036854775807'];

        yield 'overflowing string' => ['1e400'];
        yield 'negative overflowing string' => ['-1e400'];
        yield 'long integer string' => [\str_repeat('9', 400)];
        yield 'underflowing string' => ['1e-400'];
        yield 'negative underflowing string' => ['-1e-400'];
        yield 'underflowing decimal string' => ['0.0001e-400'];
        yield 'underflowing string with leading dot' => ['.1e-999'];
        yield 'long underflowing decimal string' => ['0.' . \str_repeat('0', 400) . '1'];
        yield 'string below min subnormal float' => ['2e-324'];
        yield 'infinity string' => ['INF'];
        yield 'negative infinity string' => ['-INF'];
        yield 'lowercase infinity string' => ['inf'];
        yield 'infinity word string' => ['Infinity'];
        yield 'not a number string' => ['NAN'];
        yield 'lowercase not a number string' => ['nan'];
        yield 'string with leading space' => [' 42'];
        yield 'string with trailing space' => ['42 '];
        yield 'string with surrounding spaces' => [' 42 '];
        yield 'string with trailing newline' => ["42\n"];
        yield 'string with leading tab' => ["\t1.5"];
        yield 'string with trailing form feed' => ["1.5\f"];
        yield 'string with trailing letters' => ['42abc'];
        yield 'hexadecimal string' => ['0x1A'];
        yield 'binary string' => ['0b101'];
        yield 'octal string' => ['0o17'];
        yield 'string with digit separator' => ['1_000'];
        yield 'string with decimal comma' => ['1,5'];
        yield 'string with incomplete exponent' => ['1e'];
        yield 'exponent string without mantissa' => ['e3'];
        yield 'string with double sign' => ['--1'];
        yield 'dot string' => ['.'];
        yield 'empty string' => [''];
        yield 'word string' => ['abc'];

        yield 'true' => [true];
        yield 'false' => [false];
        yield 'empty array' => [[]];
        yield 'array' => [[1.5]];
        yield 'object' => [new \stdClass()];
    }
}
