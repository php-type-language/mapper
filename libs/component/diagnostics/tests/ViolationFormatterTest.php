<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Diagnostics\Path\Path;
use TypeLang\Mapper\Diagnostics\Path\Segment\KeySegment;
use TypeLang\Mapper\Diagnostics\Printer\Value\ShortValuePrinter;
use TypeLang\Mapper\Diagnostics\Tests\Stub\ExtensionViolationCodeStub;
use TypeLang\Mapper\Diagnostics\Tests\Stub\StringableStub;
use TypeLang\Mapper\Diagnostics\Violation;
use TypeLang\Mapper\Diagnostics\ViolationCode;
use TypeLang\Mapper\Diagnostics\ViolationFormatter;

final class ViolationFormatterTest extends TestCase
{
    /**
     * @return iterable<non-empty-string, array{Violation, string}>
     */
    public static function violationDataProvider(): iterable
    {
        yield 'invalid type' => [
            new Violation(ViolationCode::InvalidType, ['expected' => 'int'], new Path(), 'abc'),
            'Value must be of type "int", but "abc" given',
        ];
        yield 'code of extension' => [
            new Violation(ExtensionViolationCodeStub::TooShort, ['min' => '3'], new Path(), 'ab'),
            'Value must be at least 3 characters long, "ab" given',
        ];
        yield 'invalid key' => [
            new Violation(ViolationCode::InvalidKey, ['expected' => 'int'], new Path(), [1]),
            'Key must be of type "int", but array(1) given',
        ];
        yield 'missing field' => [
            new Violation(ViolationCode::MissingField, ['field' => 'id'], new Path([new KeySegment('id')])),
            'Required field "id" is missing',
        ];
        yield 'unexpected field' => [
            new Violation(ViolationCode::UnexpectedField, ['field' => 'x'], new Path([new KeySegment('x')]), 1),
            'Unexpected field "x"',
        ];
        yield 'invalid value' => [
            new Violation(ViolationCode::InvalidValue, ['message' => 'Too short'], new Path(), 'a'),
            'Too short',
        ];
        yield 'unknown placeholder stays' => [
            new Violation(ViolationCode::InvalidType, [], new Path(), 1),
            'Value must be of type "{{expected}}", but 1 given',
        ];
        yield 'user input is not expanded' => [
            new Violation(ViolationCode::InvalidKey, ['expected' => 'int'], new Path(), '{{expected}}'),
            'Key must be of type "int", but "{{expected}}" given',
        ];
        yield 'exception text is not expanded' => [
            new Violation(ViolationCode::InvalidValue, ['message' => '{{value}}'], new Path(), 1),
            '{{value}}',
        ];
    }

    #[DataProvider('violationDataProvider')]
    public function testViolationIsRenderedWithoutPath(Violation $violation, string $expected): void
    {
        self::assertSame($expected, (new ViolationFormatter())->format($violation));
    }

    public function testStringableParameterIsConvertedOnRender(): void
    {
        $expected = new StringableStub('list<int>');
        $violation = new Violation(ViolationCode::InvalidType, ['expected' => $expected], new Path(), 42);

        self::assertSame(0, $expected->conversions);
        self::assertSame(
            'Value must be of type "list<int>", but 42 given',
            (new ViolationFormatter())->format($violation),
        );
        self::assertSame(1, $expected->conversions);
    }

    public function testValuePrinterIsConfigurable(): void
    {
        $violation = new Violation(ViolationCode::InvalidType, ['expected' => 'int'], new Path(), 'secret');

        $text = (new ViolationFormatter(new ShortValuePrinter(0)))->format($violation);

        self::assertSame('Value must be of type "int", but string(6) given', $text);
    }

    public function testTemplateOfViolationReplacesTemplateOfCode(): void
    {
        $violation = (new Violation(ViolationCode::InvalidType, ['expected' => 'int'], new Path(), 'x'))
            ->withTemplate('Age is a number ({{expected}}), {{value}} is not');

        self::assertSame('Age is a number (int), "x" is not', (new ViolationFormatter())->format($violation));
    }
}
