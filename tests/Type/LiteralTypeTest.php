<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Tests\TestCase;

final class LiteralTypeTest extends TestCase
{
    /**
     * @return iterable<non-empty-string, array{
     *     non-empty-string,
     *     mixed,
     *     int|float|string|bool,
     * }>
     */
    public static function denormalizableDataProvider(): iterable
    {
        yield 'integer' => ['42', 42, 42];
        yield 'negative integer' => ['-7', -7, -7];
        yield 'integer from string' => ['42', '42', 42];
        yield 'float' => ['4.5', 4.5, 4.5];
        yield 'float from string' => ['4.5', '4.5', 4.5];
        yield 'single-quoted string' => ["'active'", 'active', 'active'];
        yield 'double-quoted string' => ['"active"', 'active', 'active'];
        yield 'true' => ['true', true, true];
        yield 'true from word' => ['true', 'yes', true];
        yield 'false' => ['false', false, false];
    }

    /**
     * @return iterable<non-empty-string, array{
     *     non-empty-string,
     *     mixed,
     *     non-empty-string,
     * }>
     */
    public static function nonDenormalizableDataProvider(): iterable
    {
        yield 'other integer' => ['42', 43, '$: Value must be of type "42", but 43 given'];
        yield 'other string' => ["'active'", 'banned', '$: Value must be of type "\'active\'", but "banned" given'];
        yield 'opposite boolean' => ['true', false, '$: Value must be of type "true", but false given'];
        yield 'value of other type' => ['42', [], '$: Value must be of type "42", but array(0) given'];
    }

    /**
     * @param non-empty-string $type
     */
    #[DataProvider('denormalizableDataProvider')]
    public function testDenormalizesLiteralAndItsForms(string $type, mixed $input, int|float|string|bool $expected): void
    {
        self::assertDenormalizes($type, $input, $expected);
    }

    /**
     * @param non-empty-string $type
     * @param non-empty-string $message
     */
    #[DataProvider('nonDenormalizableDataProvider')]
    public function testRejectsOtherValue(string $type, mixed $input, string $message): void
    {
        self::assertDenormalizationFails($type, $input, $message);
    }

    public function testStrictContextRejectsOtherForm(): void
    {
        self::assertDenormalizationFails(
            '42',
            '42',
            '$: Value must be of type "42", but "42" given',
            self::createMapper(isStrict: true),
        );
    }

    public function testUnionOfLiteralsIsEnumeration(): void
    {
        self::assertDenormalizes("'draft'|'published'", 'published', 'published');
        self::assertDenormalizationFails(
            "'draft'|'published'",
            'deleted',
            '$: Value must be of type "\'draft\'|\'published\'", but "deleted" given',
        );
    }

    public function testNormalizesLiteralAsIs(): void
    {
        self::assertNormalizes("'active'", 'active', 'active');
        self::assertNormalizationFails('42', '42', '$: Value must be of type "42", but "42" given');
    }
}
