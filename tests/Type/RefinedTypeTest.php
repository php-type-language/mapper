<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Tests\TestCase;

final class RefinedTypeTest extends TestCase
{
    public function testDenormalizesValueSatisfyingChecks(): void
    {
        self::assertDenormalizes('non-empty-string', 'x', 'x');
    }

    public function testChecksValueAfterDecoratedType(): void
    {
        self::assertDenormalizes('non-empty-string', 42, '42');
    }

    public function testRejectsValueFailingCheck(): void
    {
        self::assertDenormalizationFails(
            'non-empty-string',
            '',
            '$: Value must be of type "non-empty-string" (non-empty)',
        );
    }

    public function testRejectsValueOfOtherTypeByDecoratedType(): void
    {
        self::assertDenormalizationFails(
            'non-empty-string',
            [],
            '$: Value must be of type "string", but array(0) given',
        );
    }

    public function testNormalizationSkipsChecks(): void
    {
        self::assertNormalizes('non-empty-string', '', '');
    }

    public function testNormalizationUsesDecoratedType(): void
    {
        self::assertNormalizationFails('non-empty-string', 42, '$: Value must be of type "string", but 42 given');
    }

    /**
     * @return iterable<non-empty-string, array{non-empty-string, mixed, mixed}>
     */
    public static function acceptedDataProvider(): iterable
    {
        yield 'positive-int: lowest' => ['positive-int', 1, 1];
        yield 'positive-int: coerced' => ['positive-int', '5', 5];
        yield 'negative-int: highest' => ['negative-int', -1, -1];
        yield 'non-positive-int: zero' => ['non-positive-int', 0, 0];
        yield 'non-negative-int: zero' => ['non-negative-int', 0, 0];
        yield 'int range: lower bound' => ['int<-5, 5>', -5, -5];
        yield 'int range: upper bound' => ['int<-5, 5>', 5, 5];
        yield 'int range: open lower bound' => ['int<min, 0>', \PHP_INT_MIN, \PHP_INT_MIN];
        yield 'int range: open upper bound' => ['int<0, max>', \PHP_INT_MAX, \PHP_INT_MAX];
        yield 'int range: both bounds open' => ['int<min, max>', -1, -1];
        yield 'numeric-string: float' => ['numeric-string', '-1.5e3', '-1.5e3'];
        yield 'numeric-string: coerced' => ['numeric-string', 42, '42'];
        yield 'non-empty-list' => ['non-empty-list', [1], [1]];
        yield 'non-empty-list of type' => ['non-empty-list<int>', ['1'], [1]];
        yield 'non-empty-array' => ['non-empty-array', ['a' => 1], ['a' => 1]];
        yield 'non-empty-array of key and value' => ['non-empty-array<string, int>', ['a' => '1'], ['a' => 1]];
        yield 'non-zero-int' => ['non-zero-int', -1, -1];
        yield 'lowercase-string' => ['lowercase-string', 'a-1', 'a-1'];
        yield 'lowercase-string: empty' => ['lowercase-string', '', ''];
        yield 'non-empty-lowercase-string' => ['non-empty-lowercase-string', 'a', 'a'];
        yield 'uppercase-string' => ['uppercase-string', 'A-1', 'A-1'];
        yield 'non-empty-uppercase-string' => ['non-empty-uppercase-string', 'A', 'A'];
        yield 'non-falsy-string' => ['non-falsy-string', '00', '00'];
        yield 'truthy-string' => ['truthy-string', 'a', 'a'];
        yield 'non-empty-literal-string' => ['non-empty-literal-string', 'a', 'a'];
        yield 'literal-string' => ['literal-string', '', ''];
        yield 'class-string' => ['class-string', 'Unknown\Name', 'Unknown\Name'];
        yield 'class-string of class' => ['class-string<\stdClass>', \stdClass::class, \stdClass::class];
        yield 'callable-string' => ['callable-string', 'strlen', 'strlen'];
        yield 'numeric: int' => ['numeric', 42, 42];
        yield 'numeric: float' => ['numeric', 1.5, 1.5];
        yield 'numeric: string is kept' => ['numeric', '42', '42'];
        yield 'number: int' => ['number', 42, 42];
        yield 'number: string is coerced' => ['number', '42', 42];
        yield 'real' => ['real', 1.5, 1.5];
        yield 'literal-int' => ['literal-int', 5, 5];
        yield 'empty-scalar: zero' => ['empty-scalar', 0, 0];
        yield 'empty-scalar: false' => ['empty-scalar', false, false];
        yield 'empty-scalar: zero string' => ['empty-scalar', '0', '0'];
        yield 'non-decimal-int-string: zeros' => ['non-decimal-int-string', '042', '042'];
        yield 'non-decimal-int-string: word' => ['non-decimal-int-string', 'a', 'a'];
        yield 'positive-float: lowest' => ['positive-float', 4.9E-324, 4.9E-324];
        yield 'positive-float: coerced' => ['positive-float', 1, 1.0];
        yield 'negative-float' => ['negative-float', -0.5, -0.5];
        yield 'non-positive-float: zero' => ['non-positive-float', 0.0, 0.0];
        yield 'non-negative-float: zero' => ['non-negative-float', 0.0, 0.0];
        yield 'finite-float' => ['finite-float', \PHP_FLOAT_MAX, \PHP_FLOAT_MAX];
        yield 'non-empty-mixed' => ['non-empty-mixed', ['a'], ['a']];
        yield 'non-null-mixed: zero' => ['non-null-mixed', 0, 0];
        yield 'scalar: bool' => ['scalar', false, false];
        yield 'scalar: string is kept' => ['scalar', '42', '42'];
        yield 'scalar: float' => ['scalar', 1.5, 1.5];
        yield 'non-empty-scalar' => ['non-empty-scalar', 1, 1];
        yield 'decimal-int-string' => ['decimal-int-string', '-42', '-42'];
        yield 'decimal-int-string: coerced' => ['decimal-int-string', 42, '42'];
        yield 'associative-array' => ['associative-array<string, int>', ['a' => '1'], ['a' => 1]];
        yield 'non-empty-associative-array' => ['non-empty-associative-array<int>', ['a' => 1], ['a' => 1]];
    }

    /**
     * @param non-empty-string $type
     */
    #[DataProvider('acceptedDataProvider')]
    public function testStandardRefinementAcceptsValue(string $type, mixed $input, mixed $expected): void
    {
        self::assertDenormalizes($type, $input, $expected);
    }

    /**
     * @return iterable<non-empty-string, array{
     *     non-empty-string,
     *     mixed,
     *     non-empty-string,
     * }>
     */
    public static function rejectedDataProvider(): iterable
    {
        yield 'positive-int: zero' => ['positive-int', 0, '"positive-int" (>= 1)'];
        yield 'negative-int: zero' => ['negative-int', 0, '"negative-int" (<= -1)'];
        yield 'non-positive-int: one' => ['non-positive-int', 1, '"non-positive-int" (<= 0)'];
        yield 'non-negative-int: minus one' => ['non-negative-int', -1, '"non-negative-int" (>= 0)'];
        yield 'int range: below' => ['int<1, 10>', 0, '"int<1, 10>" (between 1 and 10)'];
        yield 'int range: above' => ['int<1, 10>', 11, '"int<1, 10>" (between 1 and 10)'];
        yield 'int range: above open lower bound' => ['int<min, 0>', 1, '"int<min, 0>" (<= 0)'];
        yield 'numeric-string' => ['numeric-string', 'x', '"numeric-string" (numeric)'];
        yield 'non-empty-list' => ['non-empty-list<int>', [], '"non-empty-list<int>" (non-empty)'];
        yield 'non-empty-array' => ['non-empty-array<string, int>', [], '"non-empty-array<string, int>" (non-empty)'];
        yield 'non-zero-int' => ['non-zero-int', 0, '"non-zero-int" (non-falsy)'];
        yield 'lowercase-string' => ['lowercase-string', 'aB', '"lowercase-string" (lowercase)'];
        yield 'non-empty-lowercase-string: empty' => [
            'non-empty-lowercase-string',
            '',
            '"non-empty-lowercase-string" (non-empty)',
        ];
        yield 'uppercase-string' => ['uppercase-string', 'aB', '"uppercase-string" (uppercase)'];
        yield 'non-falsy-string: zero' => ['non-falsy-string', '0', '"non-falsy-string" (non-falsy)'];
        yield 'truthy-string: empty' => ['truthy-string', '', '"non-falsy-string" (non-falsy)'];
        yield 'non-empty-literal-string' => ['non-empty-literal-string', '', '"non-empty-literal-string" (non-empty)'];
        yield 'non-empty-scalar: zero' => ['non-empty-scalar', 0, '"non-empty-scalar" (non-falsy)'];
        yield 'empty-scalar: one' => ['empty-scalar', 1, '"empty-scalar" (falsy)'];
        yield 'non-decimal-int-string' => ['non-decimal-int-string', '42', '"non-decimal-int-string" (non-decimal-int)'];
        yield 'positive-float: zero' => ['positive-float', 0.0, '"positive-float" (> 0)'];
        yield 'negative-float: zero' => ['negative-float', 0.0, '"negative-float" (< 0)'];
        yield 'non-positive-float' => ['non-positive-float', 0.5, '"non-positive-float" (<= 0)'];
        yield 'non-negative-float' => ['non-negative-float', -0.5, '"non-negative-float" (>= 0)'];
        yield 'finite-float: infinity' => ['finite-float', \INF, '"finite-float" (finite)'];
        yield 'finite-float: not a number' => ['finite-float', \NAN, '"finite-float" (finite)'];
        yield 'non-empty-mixed: empty array' => ['non-empty-mixed', [], '"non-empty-mixed" (non-falsy)'];
        yield 'non-empty-mixed: null' => ['non-empty-mixed', null, '"non-empty-mixed" (non-falsy)'];
        yield 'non-null-mixed' => ['non-null-mixed', null, '"non-null-mixed" (non-null)'];
        yield 'non-empty-scalar: empty' => ['non-empty-scalar', '', '"non-empty-scalar" (non-falsy)'];
        yield 'decimal-int-string: zeros' => ['decimal-int-string', '042', '"decimal-int-string" (decimal-int)'];
        yield 'decimal-int-string: float' => ['decimal-int-string', '1.0', '"decimal-int-string" (decimal-int)'];
        yield 'non-empty-associative-array' => [
            'non-empty-associative-array<int>',
            [],
            '"non-empty-associative-array<int>" (non-empty)',
        ];
    }

    /**
     * @param non-empty-string $type
     * @param non-empty-string $expected
     */
    #[DataProvider('rejectedDataProvider')]
    public function testStandardRefinementRejectsValue(string $type, mixed $input, string $expected): void
    {
        self::assertDenormalizationFails($type, $input, '$: Value must be of type ' . $expected);
        self::assertDenormalizationFails(
            $type,
            $input,
            '$: Value must be of type ' . $expected,
            self::createMapper(true),
        );
    }

    public function testStrictModeRejectsValueBeforeChecks(): void
    {
        self::assertDenormalizationFails(
            'positive-int',
            '5',
            '$: Value must be of type "int", but "5" given',
            self::createMapper(true),
        );
    }

    public function testCheckFailsAtPathOfElement(): void
    {
        self::assertDenormalizationFails(
            'list<positive-int>',
            [1, 0],
            '$[1]: Value must be of type "positive-int" (>= 1)',
        );
    }

    public function testListOfOtherSyntaxIsNotRefined(): void
    {
        self::assertDenormalizes('int[]', [], []);
    }
}
