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
use TypeLang\Printer\NativeTypePrinter;
use TypeLang\Type\Identifier;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\Shape\FieldListNode;
use TypeLang\Type\Shape\NamedFieldNode;

final class ViolationFormatterTest extends TestCase
{
    /**
     * @return iterable<non-empty-string, array{Violation, string}>
     */
    public static function violationDataProvider(): iterable
    {
        $int = new NamedTypeNode(new Name('int'));

        yield 'invalid type' => [
            new Violation(ViolationCode::InvalidType, [], new Path(), 'abc', expected: $int),
            'Value must be of type int, but "abc" given',
        ];
        yield 'invalid type without expected type' => [
            new Violation(ViolationCode::InvalidType, [], new Path(), 'abc'),
            'Value "abc" is invalid',
        ];
        yield 'code of extension' => [
            new Violation(ExtensionViolationCodeStub::TooShort, ['min' => '3'], new Path(), 'ab'),
            'Value must be at least 3 characters long, "ab" given',
        ];
        yield 'invalid key' => [
            new Violation(ViolationCode::InvalidKey, [], new Path(), [1], expected: $int),
            'Key must be of type int, but array(1) given',
        ];
        yield 'invalid key without expected type' => [
            new Violation(ViolationCode::InvalidKey, [], new Path(), 'a'),
            'Key "a" is invalid',
        ];
        yield 'failed check' => [
            new Violation(ViolationCode::FailedCheck, ['check' => '>= 1'], new Path(), 0, expected: $int),
            'Value must be of type int (>= 1)',
        ];
        yield 'failed check without expected type' => [
            new Violation(ViolationCode::FailedCheck, ['check' => '>= 1'], new Path(), 0),
            'Value 0 is invalid (>= 1)',
        ];
        yield 'missing field' => [
            new Violation(ViolationCode::MissingField, ['field' => 'id'], new Path([new KeySegment('id')])),
            'Required field "id" is missing',
        ];
        yield 'unexpected field' => [
            new Violation(ViolationCode::UnexpectedField, ['field' => 'x'], new Path([new KeySegment('x')]), 1),
            'Unexpected field "x"',
        ];
        yield 'invalid value hides its cause' => [
            new Violation(ViolationCode::InvalidValue, [], new Path(), 'a', cause: new \DomainException('SQL')),
            'Value "a" is rejected',
        ];
        yield 'user input is not expanded' => [
            new Violation(ViolationCode::InvalidKey, [], new Path(), '{{expected}}', expected: $int),
            'Key must be of type int, but "{{expected}}" given',
        ];
    }

    #[DataProvider('violationDataProvider')]
    public function testViolationIsRenderedWithoutPath(Violation $violation, string $expected): void
    {
        self::assertSame($expected, self::createFormatter()->format($violation));
    }

    public function testStringableParameterIsConvertedOnRender(): void
    {
        $min = new StringableStub('3');
        $violation = new Violation(ExtensionViolationCodeStub::TooShort, ['min' => $min], new Path(), 'ab');

        self::assertSame(0, $min->conversions);
        self::assertSame(
            'Value must be at least 3 characters long, "ab" given',
            self::createFormatter()->format($violation),
        );
        self::assertSame(1, $min->conversions);
    }

    public function testExpectedTypeIsPrintedInOneLine(): void
    {
        $shape = new NamedTypeNode(new Name('array'), fields: new FieldListNode([
            new NamedFieldNode(new Identifier('id'), new NamedTypeNode(new Name('int'))),
            new NamedFieldNode(new Identifier('name'), new NamedTypeNode(new Name('string'))),
        ]));
        $violation = new Violation(ViolationCode::InvalidType, [], new Path(), 1, expected: $shape);

        self::assertSame(
            'Value must be of type array{id: int, name: string}, but 1 given',
            self::createFormatter()->format($violation),
        );
    }

    public function testTypePrinterIsConfigurable(): void
    {
        $violation = new Violation(
            ViolationCode::InvalidType,
            [],
            new Path(),
            1,
            expected: new NamedTypeNode(new Name('non-empty-string')),
        );

        self::assertSame(
            'Value must be of type string, but 1 given',
            self::createFormatter(types: new NativeTypePrinter())->format($violation),
        );
    }

    public function testValuePrinterIsConfigurable(): void
    {
        $violation = new Violation(ViolationCode::InvalidType, [], new Path(), 'secret');

        $text = self::createFormatter(new ShortValuePrinter(0))->format($violation);

        self::assertSame('Value string(6) is invalid', $text);
    }

    public function testTemplateOfViolationReplacesTemplateOfCode(): void
    {
        $violation = (new Violation(
            ViolationCode::InvalidType,
            [],
            new Path(),
            'x',
            expected: new NamedTypeNode(new Name('int')),
        ))->withTemplate('Age is a number ({{expected}}), {{value}} is not');

        self::assertSame('Age is a number (int), "x" is not', self::createFormatter()->format($violation));
    }

    public function testDeclaredTypeIsPrintedOnRequest(): void
    {
        $violation = new Violation(
            ViolationCode::InvalidType,
            [],
            new Path(),
            'x',
            declared: new NamedTypeNode(Name::from('App\User')),
            expected: new NamedTypeNode(new Name('object')),
        );

        self::assertSame(
            'Value must be of type object, but "x" given',
            self::createFormatter()->format($violation),
        );
        self::assertSame(
            'Value must be of type App\User, but "x" given',
            self::createFormatter(shouldPrintDeclaredType: true)->format($violation),
        );
    }

    public function testUnknownExpectedTypeInTemplateOfUserIsEmpty(): void
    {
        $violation = (new Violation(ViolationCode::InvalidType, [], new Path(), 'x'))
            ->withTemplate('Age is a number ({{expected}}), {{value}} is not');

        self::assertSame('Age is a number (), "x" is not', self::createFormatter()->format($violation));
    }

    public function testCheckOfViolationOfAnotherKindInTemplateOfUserIsEmpty(): void
    {
        $template = 'Age {{value}} must be {{expected}} ({{check}})';
        $type = new NamedTypeNode(new Name('positive-int'));
        $failed = (new Violation(ViolationCode::FailedCheck, ['check' => '>= 1'], new Path(), -5, expected: $type))
            ->withTemplate($template);
        $invalid = (new Violation(ViolationCode::InvalidType, [], new Path(), 'x', expected: $type))
            ->withTemplate($template);

        self::assertSame('Age -5 must be positive-int (>= 1)', self::createFormatter()->format($failed));
        self::assertSame('Age "x" must be positive-int ()', self::createFormatter()->format($invalid));
    }

    public function testTemplateWithoutExpectedTypeIsKeptForUnknownType(): void
    {
        $violation = (new Violation(ViolationCode::InvalidType, [], new Path(), 'x'))
            ->withTemplate('Age is a number, {{value}} is not');

        self::assertSame('Age is a number, "x" is not', self::createFormatter()->format($violation));
    }
}
