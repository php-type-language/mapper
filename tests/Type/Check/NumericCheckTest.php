<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Check;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Tests\TestCase;
use TypeLang\Mapper\Type\Check\NumericCheck;

final class NumericCheckTest extends TestCase
{
    /**
     * @return iterable<non-empty-string, array{mixed, bool}>
     */
    public static function valueDataProvider(): iterable
    {
        yield 'integer string' => ['42', true];
        yield 'negative float string' => ['-1.5', true];
        yield 'exponent' => ['1e3', true];
        yield 'surrounding whitespace' => [' 42 ', true];
        yield 'word' => ['x', false];
        yield 'empty string' => ['', false];
        yield 'hexadecimal' => ['0x1A', false];
        yield 'integer' => [42, false];
    }

    #[DataProvider('valueDataProvider')]
    public function testAcceptsStringOfNumber(mixed $value, bool $expected): void
    {
        self::assertSame($expected, (new NumericCheck())->isValid($value));
    }
}
