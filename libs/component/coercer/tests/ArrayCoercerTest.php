<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Coercer\Tests;

use TypeLang\Mapper\Coercer\ArrayCoercer;
use TypeLang\Mapper\Coercer\Tests\Stub\InheritedStdClassStub;

final class ArrayCoercerTest extends TestCase
{
    protected static function createCoercer(): ArrayCoercer
    {
        return new ArrayCoercer();
    }

    /**
     * @return iterable<non-empty-string, array{mixed, array<array-key, mixed>}>
     */
    public static function coercibleValueDataProvider(): iterable
    {
        yield 'empty array' => [[], []];
        yield 'list' => [[1, 2], [1, 2]];
        yield 'map' => [['a' => 1], ['a' => 1]];
        yield 'array with gaps' => [[1 => 'a'], [1 => 'a']];

        yield 'empty object' => [new \stdClass(), []];
        yield 'object' => [(object) ['a' => 1], ['a' => 1]];
        yield 'object with numeric property' => [(object) ['0' => 'a'], [0 => 'a']];
        yield 'nested object' => [(object) ['a' => (object) []], ['a' => (object) []]];
    }

    /**
     * @return iterable<non-empty-string, array{mixed}>
     */
    public static function rejectedValueDataProvider(): iterable
    {
        yield 'stdClass subclass object' => [new InheritedStdClassStub()];
        yield 'int' => [42];
        yield 'string' => ['abc'];
        yield 'empty string' => [''];
        yield 'true' => [true];
        yield 'false' => [false];
        yield 'float' => [1.5];
    }
}
