<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Tests\TestCase;

final class BoolTypeTest extends TestCase
{
    /**
     * @return iterable<non-empty-string, array{mixed, bool}>
     */
    public static function denormalizableDataProvider(): iterable
    {
        yield 'true' => [true, true];
        yield 'false' => [false, false];
        yield 'one' => [1, true];
        yield 'zero' => [0, false];
        yield 'one string' => ['1', true];
        yield 'word true in upper case' => ['TRUE', true];
        yield 'word off' => ['off', false];
    }

    /**
     * @return iterable<non-empty-string, array{mixed, non-empty-string}>
     */
    public static function nonDenormalizableDataProvider(): iterable
    {
        yield 'two' => [2, '$: Value must be of type "bool", but 2 given'];
        yield 'float one' => [1.0, '$: Value must be of type "bool", but 1.0 given'];
        yield 'empty string' => ['', '$: Value must be of type "bool", but "" given'];
        yield 'ambiguous word' => ['y', '$: Value must be of type "bool", but "y" given'];
        yield 'null' => [null, '$: Value must be of type "bool", but null given'];
    }

    #[DataProvider('denormalizableDataProvider')]
    public function testDenormalizesBooleanAndItsForms(mixed $input, bool $expected): void
    {
        self::assertDenormalizes('bool', $input, $expected);
    }

    /**
     * @param non-empty-string $message
     */
    #[DataProvider('nonDenormalizableDataProvider')]
    public function testRejectsValueThatIsNotBooleanForm(mixed $input, string $message): void
    {
        self::assertDenormalizationFails('bool', $input, $message);
    }

    public function testStrictContextRejectsOtherForm(): void
    {
        self::assertDenormalizationFails(
            'bool',
            1,
            '$: Value must be of type "bool", but 1 given',
            self::createMapper(isStrict: true),
        );
    }

    public function testBooleanIsAliasOfBool(): void
    {
        self::assertDenormalizes('boolean', 'yes', true);
    }

    public function testNormalizesBooleanAsIs(): void
    {
        self::assertNormalizes('bool', false, false);
        self::assertNormalizationFails('bool', 1, '$: Value must be of type "bool", but 1 given');
    }
}
