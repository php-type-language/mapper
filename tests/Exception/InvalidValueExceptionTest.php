<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Exception;

use TypeLang\Mapper\Diagnostics\Path\Segment\KeySegment;
use TypeLang\Mapper\Exception\InvalidValueException;
use TypeLang\Mapper\Tests\TestCase;

final class InvalidValueExceptionTest extends TestCase
{
    public function testWrappedErrorIsNotHeldByTraceOfNewOne(): void
    {
        $inner = InvalidValueException::becauseInvalidType('int', 'x');
        $outer = InvalidValueException::wrapWithPath($inner, new KeySegment(0), 'x');

        foreach ($outer->getTrace() as $frame) {
            foreach ($frame['args'] ?? [] as $argument) {
                self::assertNotSame($inner, $argument);
            }
        }

        self::assertSame('$[0]: Value must be of type "int", but "x" given', $outer->getMessage());
    }

    public function testTemplateReplacesMessagesOfValueItselfOnly(): void
    {
        $error = InvalidValueException::wrapWithPath(
            InvalidValueException::wrapWithPath(
                InvalidValueException::becauseInvalidType('int', 'x'),
                new KeySegment('nested'),
                'x',
            ),
            new KeySegment('outer'),
            [],
            'Outer is invalid',
        );

        self::assertSame('$.outer.nested: Value must be of type "int", but "x" given', $error->getMessage());

        $error = InvalidValueException::wrapWithPath(
            InvalidValueException::becauseInvalidType('int', 'x'),
            new KeySegment('outer'),
            'x',
            'Outer is invalid',
        );

        self::assertSame('$.outer: Outer is invalid', $error->getMessage());
    }

    public function testCollectsUntilLimitAndKeepsSingleErrorAsIs(): void
    {
        $violations = [];
        $first = InvalidValueException::becauseInvalidType('int', 'a');

        InvalidValueException::collect($violations, $first, 3);
        InvalidValueException::collect($violations, InvalidValueException::becauseInvalidType('int', 'b'), 3);

        self::assertCount(2, $violations);

        $error = self::captureThrowable(static function () use (&$violations): void {
            InvalidValueException::collect($violations, InvalidValueException::becauseInvalidType('int', 'c'), 3);
        });

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertCount(3, $error->violations);

        $single = [];

        self::assertSame($first, self::captureThrowable(static function () use (&$single, $first): void {
            InvalidValueException::collect($single, $first, 1);
        }));
    }
}
