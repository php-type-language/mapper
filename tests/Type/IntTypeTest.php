<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Tests\TestCase;

final class IntTypeTest extends TestCase
{
    /**
     * @return iterable<non-empty-string, array{mixed, int}>
     */
    public static function denormalizableDataProvider(): iterable
    {
        yield 'integer' => [42, 42];
        yield 'integer string' => ['42', 42];
        yield 'negative integer string' => ['-7', -7];
        yield 'decimal string with zero fraction' => ['42.0', 42];
        yield 'integral float' => [42.0, 42];
    }

    /**
     * @return iterable<non-empty-string, array{mixed, non-empty-string}>
     */
    public static function nonDenormalizableDataProvider(): iterable
    {
        yield 'padded string' => [' 42', '$: Value must be of type "int", but " 42" given'];
        yield 'word' => ['x', '$: Value must be of type "int", but "x" given'];
        yield 'fractional float' => [42.5, '$: Value must be of type "int", but 42.5 given'];
        yield 'boolean' => [true, '$: Value must be of type "int", but true given'];
        yield 'null' => [null, '$: Value must be of type "int", but null given'];
        yield 'array' => [[], '$: Value must be of type "int", but array(0) given'];
    }

    #[DataProvider('denormalizableDataProvider')]
    public function testDenormalizesIntegerAndItsForms(mixed $input, int $expected): void
    {
        self::assertDenormalizes('int', $input, $expected);
    }

    /**
     * @param non-empty-string $message
     */
    #[DataProvider('nonDenormalizableDataProvider')]
    public function testRejectsValueThatIsNotIntegerForm(mixed $input, string $message): void
    {
        self::assertDenormalizationFails('int', $input, $message);
    }

    public function testStrictContextRejectsIntegerString(): void
    {
        self::assertDenormalizationFails(
            'int',
            '42',
            '$: Value must be of type "int", but "42" given',
            self::createMapper(isStrict: true),
        );
    }

    public function testIntegerIsAliasOfInt(): void
    {
        self::assertDenormalizes('integer', '42', 42);
    }

    public function testNormalizesIntegerAsIs(): void
    {
        self::assertNormalizes('int', 42, 42);
    }

    public function testNormalizationDoesNotCoerce(): void
    {
        self::assertNormalizationFails('int', '42', '$: Value must be of type "int", but "42" given');
    }
}
