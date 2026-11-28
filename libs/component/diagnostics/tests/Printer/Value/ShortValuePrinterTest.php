<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Tests\Printer\Value;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Diagnostics\Printer\Value\ShortValuePrinter;
use TypeLang\Mapper\Diagnostics\RedactedValue;
use TypeLang\Mapper\Diagnostics\Tests\Stub\PriorityStub;
use TypeLang\Mapper\Diagnostics\Tests\Stub\StatusStub;
use TypeLang\Mapper\Diagnostics\Tests\Stub\StringableStub;
use TypeLang\Mapper\Diagnostics\Tests\TestCase;

final class ShortValuePrinterTest extends TestCase
{
    /**
     * @return iterable<non-empty-string, array{mixed, non-empty-string}>
     */
    public static function scalarDataProvider(): iterable
    {
        yield 'null' => [null, 'null'];
        yield 'true' => [true, 'true'];
        yield 'false' => [false, 'false'];
        yield 'int' => [42, '42'];
        yield 'negative int' => [-7, '-7'];
        yield 'float' => [1.5, '1.5'];
        yield 'integral float' => [1.0, '1.0'];
        yield 'float sum' => [0.1 + 0.2, '0.30000000000000004'];
        yield 'large float' => [1e25, '1.0E+25'];
        yield 'negative zero' => [-0.0, '-0.0'];
        yield 'infinity' => [\INF, 'INF'];
        yield 'negative infinity' => [-\INF, '-INF'];
        yield 'not a number' => [\NAN, 'NAN'];
    }

    #[DataProvider('scalarDataProvider')]
    public function testScalarIsPrintedByValue(mixed $value, string $expected): void
    {
        self::assertSame($expected, (new ShortValuePrinter())->print($value));
    }

    public function testRedactedValueIsPrintedWithoutQuotes(): void
    {
        self::assertSame('*redacted*', (new ShortValuePrinter())->print(new RedactedValue()));
    }

    /**
     * @return iterable<non-empty-string, array{string, non-empty-string}>
     */
    public static function stringDataProvider(): iterable
    {
        yield 'empty' => ['', '""'];
        yield 'plain' => ['abc', '"abc"'];
        yield 'double quote' => ['say "hi"', '"say \"hi\""'];
        yield 'single quote' => ['it\'s', '"it\'s"'];
        yield 'backslash' => ['App\User', '"App\\\\User"'];
        yield 'named controls' => ["a\n\t\r", '"a\n\t\r"'];
        yield 'other controls' => ["\x00\x01\x7f", '"\000\001\177"'];
        yield 'exactly at the limit' => [\str_repeat('a', 40), '"' . \str_repeat('a', 40) . '"'];
        yield 'over the limit' => [\str_repeat('a', 41), '"' . \str_repeat('a', 40) . '"...'];
        yield 'unicode over the limit counts characters' => [
            \str_repeat('é', 50),
            '"' . \str_repeat('é', 40) . '"...',
        ];
        yield 'unicode at the limit is not cut' => [\str_repeat('é', 40), '"' . \str_repeat('é', 40) . '"'];
        yield 'escaping after the cut' => [\str_repeat("\n", 41), '"' . \str_repeat('\n', 40) . '"...'];
        yield 'invalid utf-8' => ["ok\xff", 'string(3)'];
        yield 'invalid utf-8 behind the limit' => [\str_repeat('a', 100) . "\xff", 'string(101)'];
        yield 'long string is cut before the check' => [
            \str_repeat('a', 1000) . "\xff",
            '"' . \str_repeat('a', 40) . '"...',
        ];
        yield 'character cut by the bytes is dropped' => [
            'a' . \str_repeat('é', 1000),
            '"a' . \str_repeat('é', 39) . '"...',
        ];
    }

    #[DataProvider('stringDataProvider')]
    public function testStringIsPrintedQuotedAndCut(string $value, string $expected): void
    {
        self::assertSame($expected, (new ShortValuePrinter())->print($value));
    }

    /**
     * @return iterable<non-empty-string, array{
     *     string,
     *     int<0, max>,
     *     non-empty-string,
     * }>
     */
    public static function stringLimitDataProvider(): iterable
    {
        yield 'hidden' => ['secret', 0, 'string(6)'];
        yield 'hidden counts bytes' => ['café', 0, 'string(5)'];
        yield 'short limit' => ['abcdef', 3, '"abc"...'];
        yield 'within the limit' => ['abc', 3, '"abc"'];
    }

    /**
     * @param int<0, max> $limit
     */
    #[DataProvider('stringLimitDataProvider')]
    public function testStringLimitIsConfigurable(string $value, int $limit, string $expected): void
    {
        self::assertSame($expected, (new ShortValuePrinter($limit))->print($value));
    }

    /**
     * @return iterable<non-empty-string, array{mixed, non-empty-string}>
     */
    public static function containerDataProvider(): iterable
    {
        yield 'empty array' => [[], 'array(0)'];
        yield 'array' => [['a' => 1, 'b' => [1, 2]], 'array(2)'];
        yield 'unit enum' => [StatusStub::Active, StatusStub::class . '::Active'];
        yield 'backed enum' => [PriorityStub::High, PriorityStub::class . '::High'];
        yield 'object' => [new \ArrayObject(), 'object(ArrayObject)'];
        yield 'stringable object' => [new StringableStub('text'), 'object(' . StringableStub::class . ')'];
        yield 'closure' => [static fn(): int => 1, 'object(Closure)'];
        yield 'anonymous class' => [new class {}, 'object(class@anonymous)'];
        yield 'anonymous subclass' => [new class extends \ArrayObject {}, 'object(ArrayObject@anonymous)'];
    }

    #[DataProvider('containerDataProvider')]
    public function testContainerIsPrintedByKind(mixed $value, string $expected): void
    {
        self::assertSame($expected, (new ShortValuePrinter())->print($value));
    }

    public function testStringableObjectIsNotConverted(): void
    {
        $object = new StringableStub('text');

        (new ShortValuePrinter())->print($object);

        self::assertSame(0, $object->conversions);
    }

    public function testResourceIsPrintedByType(): void
    {
        $resource = \fopen('php://memory', 'rb');
        self::assertIsResource($resource);

        self::assertSame('resource(stream)', (new ShortValuePrinter())->print($resource));

        \fclose($resource);

        self::assertSame('resource(closed)', (new ShortValuePrinter())->print($resource));
    }
}
