<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Tests\Printer\Path;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Diagnostics\Path\Path;
use TypeLang\Mapper\Diagnostics\Path\Segment\ConditionalBranchSegment;
use TypeLang\Mapper\Diagnostics\Path\Segment\ConditionalBranchSegment\ConditionalBranch;
use TypeLang\Mapper\Diagnostics\Path\Segment\IntersectionLeafSegment;
use TypeLang\Mapper\Diagnostics\Path\Segment\KeySegment;
use TypeLang\Mapper\Diagnostics\Path\Segment\ObjectSegment;
use TypeLang\Mapper\Diagnostics\Path\Segment\PropertySegment;
use TypeLang\Mapper\Diagnostics\Path\Segment\UnionLeafSegment;
use TypeLang\Mapper\Diagnostics\Printer\Path\JsonPathPrinter;
use TypeLang\Mapper\Diagnostics\Tests\Stub\UnknownSegmentStub;
use TypeLang\Mapper\Diagnostics\Tests\Stub\UserStub;
use TypeLang\Mapper\Diagnostics\Tests\TestCase;

final class JsonPathPrinterTest extends TestCase
{
    /**
     * @return iterable<non-empty-string, array{int|string, non-empty-string}>
     */
    public static function keyDataProvider(): iterable
    {
        yield 'identifier' => ['name', '$.name'];
        yield 'identifier with underscore and digits' => ['_first_name2', '$._first_name2'];
        yield 'index' => [3, '$[3]'];
        yield 'zero index' => [0, '$[0]'];
        yield 'negative index' => [-1, '$[\'-1\']'];
        yield 'name with space' => ['first name', '$[\'first name\']'];
        yield 'name starting with digit' => ['1st', '$[\'1st\']'];
        yield 'numeric string' => ['0', '$[\'0\']'];
        yield 'empty name' => ['', '$[\'\']'];
        yield 'single quote' => ['it\'s', '$[\'it\\\'s\']'];
        yield 'double quote' => ['say "hi"', '$[\'say "hi"\']'];
        yield 'backslash' => ['App\User', '$[\'App\\\\User\']'];
        yield 'unicode' => ["\u{1F600}", "\$['\u{1F600}']"];
        yield 'named controls' => ["\x08\t\n\f\r", '$[\'\b\t\n\f\r\']'];
        yield 'other controls' => ["\x00\x01\x1f", '$[\'\u0000\u0001\u001f\']'];
        yield 'delete is not a control' => ["\x7f", "\$['\x7f']"];
        yield 'trailing newline is not an identifier' => ["name\n", '$[\'name\n\']'];
    }

    #[DataProvider('keyDataProvider')]
    public function testKeyIsPrintedAsSelector(int|string $key, string $expected): void
    {
        $path = new Path([new KeySegment($key)]);

        self::assertSame($expected, (new JsonPathPrinter())->print($path));
    }

    /**
     * @return iterable<non-empty-string, array{
     *     non-empty-string,
     *     non-empty-string,
     * }>
     */
    public static function propertyDataProvider(): iterable
    {
        yield 'identifier' => ['name', '$.name'];
        yield 'name with space' => ['first name', '$[\'first name\']'];
        yield 'single quote' => ['it\'s', '$[\'it\\\'s\']'];
    }

    /**
     * @param non-empty-string $property
     */
    #[DataProvider('propertyDataProvider')]
    public function testPropertyIsPrintedAsNameSelector(string $property, string $expected): void
    {
        $path = new Path([new PropertySegment($property)]);

        self::assertSame($expected, (new JsonPathPrinter())->print($path));
    }

    public function testRootIsPrintedAsDollar(): void
    {
        self::assertSame('$', (new JsonPathPrinter())->print(new Path()));
    }

    public function testMixedSegmentsArePrintedInOrder(): void
    {
        $path = new Path([
            new PropertySegment('orders'),
            new KeySegment(2),
            new KeySegment('line items'),
            new KeySegment(0),
            new PropertySegment('name'),
        ]);

        self::assertSame("\$.orders[2]['line items'][0].name", (new JsonPathPrinter())->print($path));
    }

    public function testTypeSegmentsAreSkipped(): void
    {
        $path = new Path([
            new UnionLeafSegment(0),
            new ObjectSegment(UserStub::class),
            new PropertySegment('items'),
            new KeySegment(0),
            new ConditionalBranchSegment(ConditionalBranch::Then),
            new IntersectionLeafSegment(1),
            new KeySegment('id'),
        ]);

        self::assertSame('$.items[0].id', (new JsonPathPrinter())->print($path));
    }

    public function testUnknownSegmentIsSkipped(): void
    {
        $path = new Path([new KeySegment('items'), new UnknownSegmentStub(), new KeySegment(0)]);

        self::assertSame('$.items[0]', (new JsonPathPrinter())->print($path));
    }
}
