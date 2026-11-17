<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use TypeLang\Mapper\Tests\TestCase;

final class ArrayTypeTest extends TestCase
{
    public function testDenormalizesEachElementKeepingKeys(): void
    {
        self::assertDenormalizes('array<string, int>', ['a' => '1', 'b' => 2], ['a' => 1, 'b' => 2]);
    }

    public function testDenormalizesObjectOfNamedKeys(): void
    {
        self::assertDenormalizes('array<string, int>', (object) ['a' => 1], ['a' => 1]);
    }

    public function testStringKeyTypeAcceptsKeyTurnedIntoIntegerByPhp(): void
    {
        self::assertDenormalizes('array<string, int>', ['1' => 2], [1 => 2], self::createMapper(isStrict: true));
    }

    public function testRejectsInvalidKeyWithItsPath(): void
    {
        self::assertDenormalizationFails(
            'array<int, string>',
            ['a' => 'x'],
            '$.a: Key must be of type "int", but "a" given',
        );
    }

    public function testRejectsInvalidElementWithItsKey(): void
    {
        self::assertDenormalizationFails(
            'array<string, int>',
            ['a' => 1, 'b' => 'x'],
            '$.b: Value must be of type "int", but "x" given',
        );
    }

    public function testValueTypeOfArrayWithOneArgument(): void
    {
        self::assertDenormalizes('array<int>', ['a' => '1', 5 => 2], ['a' => 1, 5 => 2]);
    }

    public function testBareArrayAcceptsAnyArray(): void
    {
        self::assertDenormalizes('array', ['a' => [1], 2 => null], ['a' => [1], 2 => null]);
    }

    public function testSquareBracketsAreArrayOfType(): void
    {
        self::assertDenormalizes('int[]', ['a' => '1', 'b' => 2], ['a' => 1, 'b' => 2]);
    }

    public function testRejectsValueThatIsNotArray(): void
    {
        self::assertDenormalizationFails('array<int>', 'x', '$: Value must be of type "array<int>", but "x" given');
    }

    public function testNormalizesEachElementKeepingKeys(): void
    {
        self::assertNormalizes('array<string, int>', ['a' => 1], ['a' => 1]);
    }

    public function testNormalizationRejectsInvalidKey(): void
    {
        self::assertNormalizationFails(
            'array<string, int>',
            [5 => 1],
            '$[5]: Key must be of type "string", but 5 given',
        );
    }

    public function testNormalizationRejectsInvalidElementWithItsKey(): void
    {
        self::assertNormalizationFails(
            'array<string, int>',
            ['a' => 1, 'b' => 'x'],
            '$.b: Value must be of type "int", but "x" given',
        );
    }

    public function testNormalizationRejectsValueThatIsNotArray(): void
    {
        self::assertNormalizationFails('array<int>', 'x', '$: Value must be of type "array<int>", but "x" given');
    }

    public function testArrayKeyIsIntegerOrString(): void
    {
        self::assertDenormalizes('array-key', 5, 5);
        self::assertDenormalizes('array-key', 'a', 'a');
        self::assertDenormalizationFails('array-key', 1.5, '$: Value must be of type "array-key", but 1.5 given');
    }
}
