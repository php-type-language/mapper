<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Tests\Exception;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Diagnostics\Path\Path;
use TypeLang\Mapper\Diagnostics\Path\Segment\KeySegment;
use TypeLang\Mapper\Diagnostics\Path\Segment\PropertySegment;
use TypeLang\Mapper\Diagnostics\Printer\Path\PathPrinterInterface;
use TypeLang\Mapper\Diagnostics\Tests\Stub\InvalidInputStubException;
use TypeLang\Mapper\Diagnostics\Tests\TestCase;
use TypeLang\Mapper\Diagnostics\Violation;
use TypeLang\Mapper\Diagnostics\ViolationCode;

final class ViolationExceptionTest extends TestCase
{
    /**
     * @return iterable<non-empty-string, array{
     *     non-empty-list<Violation>,
     *     non-empty-string,
     * }>
     */
    public static function summaryDataProvider(): iterable
    {
        $invalid = ViolationCode::InvalidType;
        $int = ['expected' => 'int'];
        $path = new Path([
            new KeySegment('items'),
            new KeySegment(3),
            new PropertySegment('name'),
        ]);

        yield 'single violation' => [
            [new Violation($invalid, $int, $path, 'x')],
            '$.items[3].name: Value must be of type "int", but "x" given',
        ];
        yield 'violation at the root' => [
            [new Violation($invalid, $int, new Path(), [])],
            '$: Value must be of type "int", but array(0) given',
        ];
        yield 'several violations' => [
            [
                new Violation(ViolationCode::MissingField, ['field' => 'id'], new Path([new KeySegment('id')])),
                new Violation($invalid, $int, new Path([new KeySegment('age')]), 'x'),
                new Violation($invalid, $int, new Path([new KeySegment('size')]), 'y'),
            ],
            '$.id: Required field "id" is missing (and 2 more)',
        ];
    }

    /**
     * @param non-empty-list<Violation> $violations
     */
    #[DataProvider('summaryDataProvider')]
    public function testMessageSummarizesViolations(array $violations, string $expected): void
    {
        $exception = InvalidInputStubException::becauseInputIsInvalid($violations);

        self::assertInstanceOf(\RuntimeException::class, $exception);
        self::assertSame($expected, $exception->getMessage());
        self::assertSame($violations, $exception->violations);
    }

    public function testSummaryUsesTheGivenPathPrinter(): void
    {
        $violation = new Violation(ViolationCode::InvalidValue, ['message' => 'Too short'], new Path(), 'a');
        $printer = new class implements PathPrinterInterface {
            public function print(Path $path): string
            {
                return 'custom';
            }
        };

        $exception = InvalidInputStubException::becauseInputIsInvalid(
            [$violation],
            pathPrinter: $printer,
        );

        self::assertSame('custom: Too short', $exception->getMessage());
    }
}
