<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Tests\TestCase;

final class NullableTypeTest extends TestCase
{
    /**
     * @return iterable<non-empty-string, array{non-empty-string}>
     */
    public static function nullableDataProvider(): iterable
    {
        yield 'nullable' => ['?int'];
        yield 'union with null' => ['int|null'];
        yield 'union with null first' => ['null|int'];
    }

    /**
     * @param non-empty-string $type
     */
    #[DataProvider('nullableDataProvider')]
    public function testDenormalizesNull(string $type): void
    {
        self::assertDenormalizes($type, null, null);
    }

    /**
     * @param non-empty-string $type
     */
    #[DataProvider('nullableDataProvider')]
    public function testDenormalizesOtherValueByType(string $type): void
    {
        self::assertDenormalizes($type, '7', 7);
    }

    /**
     * @param non-empty-string $type
     */
    #[DataProvider('nullableDataProvider')]
    public function testRejectsValueByType(string $type): void
    {
        self::assertDenormalizationFails($type, 'x', \sprintf('$: Value must be of type %s, but "x" given', $type));
    }

    public function testNormalizesNullAndValue(): void
    {
        self::assertNormalizes('?int', null, null);
        self::assertNormalizes('?int', 7, 7);
    }

    public function testNullIsTypeOfItsOwn(): void
    {
        self::assertDenormalizes('null', null, null);
        self::assertDenormalizationFails('null', 0, '$: Value must be of type null, but 0 given');
        self::assertNormalizationFails('null', 0, '$: Value must be of type null, but 0 given');
    }
}
