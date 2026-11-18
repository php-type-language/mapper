<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Tests;

use TypeLang\Mapper\Diagnostics\Path\Path;
use TypeLang\Mapper\Diagnostics\Printer\Value\ShortValuePrinter;
use TypeLang\Mapper\Diagnostics\PublicViolationFormatter;
use TypeLang\Mapper\Diagnostics\Tests\Stub\UserStub;
use TypeLang\Mapper\Diagnostics\Violation;
use TypeLang\Mapper\Diagnostics\ViolationCode;
use TypeLang\Mapper\Diagnostics\ViolationFormatter;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;

final class PublicViolationFormatterTest extends TestCase
{
    public function testExpectedTypeIsPrintedInPlaceOfDeclaredOne(): void
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
            self::createPublicFormatter()->format($violation),
        );
    }

    public function testDeclaredTypeWithoutExpectedOneIsHidden(): void
    {
        $violation = new Violation(
            ViolationCode::InvalidType,
            [],
            new Path(),
            'x',
            declared: new NamedTypeNode(Name::from('App\User')),
        );

        self::assertSame('Value "x" is invalid', self::createPublicFormatter()->format($violation));
    }

    public function testObjectValueIsPrintedWithoutItsClass(): void
    {
        $violation = new Violation(ViolationCode::InvalidType, [], new Path(), new UserStub());

        self::assertSame('Value object is invalid', self::createPublicFormatter()->format($violation));
    }

    public function testInnerFormatterIsConfigurable(): void
    {
        $violation = new Violation(ViolationCode::InvalidType, [], new Path(), 'secret');
        $formatter = self::createPublicFormatter(new ShortValuePrinter(0));

        self::assertSame('Value string(6) is invalid', $formatter->format($violation));
    }
}
