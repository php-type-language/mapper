<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Check;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Tests\TestCase;
use TypeLang\Mapper\Type\Check\IntRangeCheck;

final class IntRangeCheckTest extends TestCase
{
    /**
     * @return iterable<non-empty-string, array{IntRangeCheck, mixed, bool}>
     */
    public static function valueDataProvider(): iterable
    {
        $range = new IntRangeCheck(-1, 1);

        yield 'lower bound' => [$range, -1, true];
        yield 'upper bound' => [$range, 1, true];
        yield 'below lower bound' => [$range, -2, false];
        yield 'above upper bound' => [$range, 2, false];
        yield 'float within bounds' => [$range, 0.0, false];
        yield 'numeric string within bounds' => [$range, '0', false];
        yield 'open lower bound' => [new IntRangeCheck(max: 0), \PHP_INT_MIN, true];
        yield 'open upper bound' => [new IntRangeCheck(min: 0), \PHP_INT_MAX, true];
        yield 'both bounds open' => [new IntRangeCheck(), \PHP_INT_MIN, true];
    }

    #[DataProvider('valueDataProvider')]
    public function testChecksValueWithinBounds(IntRangeCheck $check, mixed $value, bool $expected): void
    {
        self::assertSame($expected, $check->isValid($value));
    }

    /**
     * @return iterable<non-empty-string, array{
     *     IntRangeCheck,
     *     non-empty-string,
     * }>
     */
    public static function nameDataProvider(): iterable
    {
        yield 'both bounds' => [new IntRangeCheck(1, 10), 'between 1 and 10'];
        yield 'lower bound' => [new IntRangeCheck(min: 1), '>= 1'];
        yield 'upper bound' => [new IntRangeCheck(max: -1), '<= -1'];
        yield 'no bounds' => [new IntRangeCheck(), 'any'];
    }

    /**
     * @param non-empty-string $expected
     */
    #[DataProvider('nameDataProvider')]
    public function testNameDescribesBounds(IntRangeCheck $check, string $expected): void
    {
        self::assertSame($expected, $check->description);
    }
}
