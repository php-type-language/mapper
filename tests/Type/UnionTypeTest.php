<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Tests\TestCase;

final class UnionTypeTest extends TestCase
{
    /**
     * @return iterable<non-empty-string, array{non-empty-string, mixed, mixed}>
     */
    public static function denormalizableDataProvider(): iterable
    {
        yield 'exact match of first type' => ['int|string', 42, 42];
        yield 'exact match of second type' => ['int|string', '42', '42'];
        yield 'exact match wins over coercion' => ['string|int', 42, 42];
        yield 'coercion when no exact match' => ['int|string', 42.0, 42];
        yield 'string tried last with coercion' => ['string|int', 42.0, 42];
        yield 'refined string tried last with coercion' => ['non-empty-string|int', 42.0, 42];
        yield 'string literal tried last with coercion' => ["'42'|int", 42.0, 42];
        yield 'exact string still wins' => ['string|int', '42', '42'];
        yield 'string when nothing else accepts' => ['string|int', 4.5, '4.5'];
        yield 'null among three types' => ['int|string|null', null, null];
        yield 'value among three types' => ['int|string|null', 'x', 'x'];
    }

    /**
     * @param non-empty-string $type
     */
    #[DataProvider('denormalizableDataProvider')]
    public function testDenormalizesByFirstTypeAcceptingValueExactly(string $type, mixed $input, mixed $expected): void
    {
        self::assertDenormalizes($type, $input, $expected);
    }

    public function testRejectsValueNoTypeAccepts(): void
    {
        self::assertDenormalizationFails('int|string', [], '$: Value must be of type "int|string", but array(0) given');
    }

    public function testStrictContextSkipsCoercion(): void
    {
        self::assertDenormalizationFails(
            'int|string',
            42.0,
            '$: Value must be of type "int|string", but 42.0 given',
            self::createRepository(isStrict: true),
        );
    }

    public function testNormalizesByFirstTypeAcceptingValue(): void
    {
        self::assertNormalizes('int|string', 'x', 'x');
        self::assertNormalizes('int|string', 42, 42);
    }

    public function testNormalizationRejectsValueNoTypeAccepts(): void
    {
        self::assertNormalizationFails('int|string', 4.5, '$: Value must be of type "int|string", but 4.5 given');
    }
}
