<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Coercer\Tests;

use TypeLang\Mapper\Coercer\IntCoercer;

final class IntCoercerTest extends TestCase
{
    protected static function createCoercer(): IntCoercer
    {
        return new IntCoercer();
    }

    /**
     * @return iterable<non-empty-string, array{mixed, int}>
     */
    public static function coercibleValueDataProvider(): iterable
    {
        yield 'int' => [42, 42];
        yield 'zero int' => [0, 0];
        yield 'negative int' => [-42, -42];
        yield 'max int' => [\PHP_INT_MAX, \PHP_INT_MAX];
        yield 'min int' => [\PHP_INT_MIN, \PHP_INT_MIN];

        yield 'integer string' => ['42', 42];
        yield 'zero string' => ['0', 0];
        yield 'negative integer string' => ['-42', -42];
        yield 'integer string with plus sign' => ['+42', 42];
        yield 'integer string with leading zeros' => ['007', 7];
        yield 'negative integer string with leading zeros' => ['-007', -7];
        yield 'zeros string' => ['000', 0];
        yield 'negative zero string' => ['-0', 0];
        yield 'positive zero string' => ['+0', 0];
        yield 'max int string' => ['9223372036854775807', \PHP_INT_MAX];
        yield 'min int string' => ['-9223372036854775808', \PHP_INT_MIN];
        yield 'max int string with leading zeros' => ['009223372036854775807', \PHP_INT_MAX];
        yield 'integer string above float precision' => ['9007199254740993', 9007199254740993];
        yield 'integral decimal string' => ['42.0', 42];
        yield 'decimal string with zeros fraction' => ['42.000', 42];
        yield 'decimal string with trailing dot' => ['42.', 42];
        yield 'negative integral decimal string' => ['-42.0', -42];
        yield 'negative zero decimal string' => ['-0.0', 0];
        yield 'max int decimal string' => ['9223372036854775807.0', \PHP_INT_MAX];
        yield 'decimal string above float precision' => ['9007199254740993.0', 9007199254740993];

        yield 'integral float' => [42.0, 42];
        yield 'negative integral float' => [-42.0, -42];
        yield 'zero float' => [0.0, 0];
        yield 'negative zero float' => [-0.0, 0];
        yield 'large integral float' => [1e15, 1000000000000000];
        yield 'max exact float' => [9007199254740992.0, 9007199254740992];
        yield 'min int float' => [(float) \PHP_INT_MIN, \PHP_INT_MIN];
        yield 'largest float below int overflow' => [9223372036854774784.0, 9223372036854774784];
    }

    /**
     * @return iterable<non-empty-string, array{mixed}>
     */
    public static function rejectedValueDataProvider(): iterable
    {
        yield 'string above max int' => ['9223372036854775808'];
        yield 'string below min int' => ['-9223372036854775809'];
        yield 'long integer string' => [\str_repeat('9', 1000)];
        yield 'long negative integer string' => ['-' . \str_repeat('9', 1000)];
        yield 'decimal string above max int' => ['9223372036854775808.0'];
        yield 'decimal string with non-zero fraction digit' => ['42.0001'];
        yield 'decimal string rounded to integer float' => ['0.99999999999999999999'];
        yield 'decimal string with leading dot' => ['.5'];
        yield 'zero decimal string with leading dot' => ['.0'];
        yield 'integral exponent decimal string' => ['1.0e3'];
        yield 'exponent string' => ['1e3'];
        yield 'uppercase exponent string' => ['1E3'];
        yield 'fractional string' => ['1.5'];
        yield 'string with leading space' => [' 42'];
        yield 'string with trailing space' => ['42 '];
        yield 'string with surrounding spaces' => [' 42 '];
        yield 'string with leading tab' => ["\t42"];
        yield 'string with trailing newline' => ["42\n"];
        yield 'string with leading carriage return' => ["\r42"];
        yield 'string with trailing vertical tab' => ["42\v"];
        yield 'string with leading form feed' => ["\f42"];
        yield 'string with leading non-breaking space' => ["\u{00A0}42"];
        yield 'string with trailing null byte' => ["42\0"];
        yield 'string with inner space' => ['4 2'];
        yield 'string with trailing letters' => ['42abc'];
        yield 'hexadecimal string' => ['0x1A'];
        yield 'binary string' => ['0b101'];
        yield 'octal string' => ['0o17'];
        yield 'string with digit separator' => ['1_000'];
        yield 'string with thousands separator' => ['1,000'];
        yield 'string with double sign' => ['--1'];
        yield 'string with mixed signs' => ['+-1'];
        yield 'sign string' => ['-'];
        yield 'dot string' => ['.'];
        yield 'empty string' => [''];
        yield 'word string' => ['abc'];
        yield 'infinity string' => ['INF'];
        yield 'not a number string' => ['NAN'];

        yield 'fractional float' => [1.5];
        yield 'negative fractional float' => [-1.5];
        yield 'inexact float sum' => [0.1 + 0.2];
        yield 'tiny float' => [1e-300];
        yield 'int overflow float' => [\PHP_INT_MAX + 1];
        yield 'float below min int' => [-9223372036854777856.0];
        yield 'huge float' => [1e300];
        yield 'infinity' => [\INF];
        yield 'negative infinity' => [-\INF];
        yield 'not a number' => [\NAN];

        yield 'true' => [true];
        yield 'false' => [false];
        yield 'empty array' => [[]];
        yield 'array' => [[1]];
        yield 'object' => [new \stdClass()];
    }
}
