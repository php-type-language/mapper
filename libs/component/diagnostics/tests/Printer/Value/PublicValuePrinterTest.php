<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Tests\Printer\Value;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Diagnostics\Printer\Value\PublicValuePrinter;
use TypeLang\Mapper\Diagnostics\Printer\Value\ShortValuePrinter;
use TypeLang\Mapper\Diagnostics\RedactedValue;
use TypeLang\Mapper\Diagnostics\Tests\Stub\PriorityStub;
use TypeLang\Mapper\Diagnostics\Tests\Stub\StatusStub;
use TypeLang\Mapper\Diagnostics\Tests\Stub\UserStub;
use TypeLang\Mapper\Diagnostics\Tests\TestCase;

final class PublicValuePrinterTest extends TestCase
{
    /**
     * @return iterable<non-empty-string, array{mixed, non-empty-string}>
     */
    public static function valueDataProvider(): iterable
    {
        yield 'object' => [new UserStub(), 'object'];
        yield 'closure' => [static fn(): int => 1, 'object'];
        yield 'backed enum' => [PriorityStub::High, '1'];
        yield 'unit enum' => [StatusStub::Active, '"Active"'];
        yield 'string' => ['abc', '"abc"'];
        yield 'int' => [42, '42'];
        yield 'array' => [[1, 2], 'array(2)'];
        yield 'null' => [null, 'null'];
        yield 'redacted' => [new RedactedValue(), '*redacted*'];
    }

    #[DataProvider('valueDataProvider')]
    public function testValueIsPrintedWithoutNamesOfApplication(mixed $value, string $expected): void
    {
        self::assertSame($expected, (new PublicValuePrinter())->print($value));
    }

    public function testResourceIsPrintedWithoutItsType(): void
    {
        $resource = \fopen('php://memory', 'rb');

        self::assertSame('resource', (new PublicValuePrinter())->print($resource));

        \fclose($resource);

        self::assertSame('resource', (new PublicValuePrinter())->print($resource));
    }

    public function testScalarsArePrintedByInnerPrinter(): void
    {
        self::assertSame('string(6)', (new PublicValuePrinter(new ShortValuePrinter(0)))->print('secret'));
    }
}
