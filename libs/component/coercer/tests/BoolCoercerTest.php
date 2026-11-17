<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Coercer\Tests;

use TypeLang\Mapper\Coercer\BoolCoercer;

final class BoolCoercerTest extends TestCase
{
    protected static function createCoercer(): BoolCoercer
    {
        return new BoolCoercer();
    }

    /**
     * @return iterable<non-empty-string, array{mixed, bool}>
     */
    public static function coercibleValueDataProvider(): iterable
    {
        yield 'true' => [true, true];
        yield 'false' => [false, false];

        yield 'one int' => [1, true];
        yield 'zero int' => [0, false];

        yield 'one string' => ['1', true];
        yield 'zero string' => ['0', false];

        foreach (['true', 'TRUE', 'True', 'tRuE', 'yes', 'YES', 'Yes', 'on', 'ON', 'On'] as $word) {
            yield \sprintf('%s string', $word) => [$word, true];
        }

        foreach (['false', 'FALSE', 'False', 'fAlSe', 'no', 'NO', 'No', 'off', 'OFF', 'Off'] as $word) {
            yield \sprintf('%s string', $word) => [$word, false];
        }
    }

    /**
     * @return iterable<non-empty-string, array{mixed}>
     */
    public static function rejectedValueDataProvider(): iterable
    {
        yield 'two int' => [2];
        yield 'negative one int' => [-1];
        yield 'max int' => [\PHP_INT_MAX];

        yield 'one float' => [1.0];
        yield 'zero float' => [0.0];
        yield 'negative zero float' => [-0.0];
        yield 'not a number' => [\NAN];

        yield 'empty string' => [''];
        yield 'string with leading space' => [' true'];
        yield 'string with trailing space' => ['true '];
        yield 'string with trailing newline' => ["1\n"];
        yield 'string with trailing null byte' => ["true\0"];
        yield 'decimal one string' => ['1.0'];
        yield 'negative zero string' => ['-0'];
        yield 'one string with plus sign' => ['+1'];
        yield 'one string with leading zero' => ['01'];
        yield 'zeros string' => ['00'];
        yield 'two string' => ['2'];
        yield 'y string' => ['y'];
        yield 'n string' => ['n'];
        yield 't string' => ['t'];
        yield 'f string' => ['f'];
        yield 'word string' => ['abc'];
        yield 'misspelled true string' => ['truee'];
        yield 'misspelled yes string' => ['yess'];
        yield 'enabled string' => ['enabled'];
        yield 'null string' => ['null'];
        yield 'long string' => [\str_repeat('a', 1000)];

        yield 'empty array' => [[]];
        yield 'array' => [[1]];
        yield 'object' => [new \stdClass()];
    }
}
