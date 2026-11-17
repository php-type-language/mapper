<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Context\Configuration;
use TypeLang\Mapper\Tests\Stub\AddressStub;
use TypeLang\Mapper\Tests\TestCase;

final class ShapeTypeTest extends TestCase
{
    /**
     * @return iterable<non-empty-string, array{non-empty-string, mixed, mixed}>
     */
    public static function denormalizedDataProvider(): iterable
    {
        yield 'named fields' => ['array{a: int, b: string}', ['a' => '1', 'b' => 'x'], ['a' => 1, 'b' => 'x']];
        yield 'absent optional field' => ['array{a: int, b?: string}', ['a' => 1], ['a' => 1]];
        yield 'present optional field' => ['array{a: int, b?: string}', ['a' => 1, 'b' => 'x'], ['a' => 1, 'b' => 'x']];
        yield 'other keys of sealed shape' => ['array{a: int}', ['a' => 1, 'x' => 2], ['a' => 1]];
        yield 'other keys of unsealed shape' => ['array{a: int, ...}', ['x' => 2, 'a' => 1], ['a' => 1, 'x' => 2]];
        yield 'implicit keys' => ['array{int, string}', [1, 'x'], [1, 'x']];
        yield 'literal and implicit keys' => [
            "array{'1': int, 5: int, int}",
            [1 => 1, 5 => 2, 6 => 3],
            [1 => 1, 5 => 2, 6 => 3],
        ];
        yield 'nested shape' => ['array{a: array{b: int}}', ['a' => ['b' => '2']], ['a' => ['b' => 2]]];
        yield 'empty shape' => ['array{}', ['x' => 1], []];
        yield 'object from array' => ['object{a: int}', ['a' => 1], (object) ['a' => 1]];
        yield 'object from object' => ['object{a: int}', (object) ['a' => 1, 'x' => 2], (object) ['a' => 1]];
        yield 'array from object' => ['array{a: int}', (object) ['a' => 1], ['a' => 1]];
    }

    /**
     * @param non-empty-string $type
     */
    #[DataProvider('denormalizedDataProvider')]
    public function testDenormalizesDeclaredFields(string $type, mixed $input, mixed $expected): void
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
        yield 'not an array' => ['array{a: int}', 'x', '$: Value must be of type "array{a: int}", but "x" given'];
        yield 'absent required field' => ['array{a: int}', [], '$.a: Required field "a" is missing'];
        yield 'invalid field' => ['array{a: int}', ['a' => 'x'], '$.a: Value must be of type "int", but "x" given'];
        yield 'invalid nested field' => [
            'array{a: list<int>}',
            ['a' => [1, 'x']],
            '$.a[1]: Value must be of type "int", but "x" given',
        ];
        yield 'invalid implicit field' => ['array{int}', ['x'], '$[0]: Value must be of type "int", but "x" given'];
        yield 'invalid field of object' => [
            'object{a: int}',
            ['a' => 'x'],
            '$.a: Value must be of type "int", but "x" given',
        ];
    }

    /**
     * @param non-empty-string $type
     * @param non-empty-string $message
     */
    #[DataProvider('rejectedDataProvider')]
    public function testRejectsValueNotOfShape(string $type, mixed $input, string $message): void
    {
        self::assertDenormalizationFails($type, $input, $message);
    }

    public function testNormalizesArrayShape(): void
    {
        self::assertNormalizes('array{a: int, b?: int}', ['a' => 1, 'x' => 2], ['a' => 1]);
        self::assertNormalizes('array{a: int, ...}', ['a' => 1, 'x' => 2], ['a' => 1, 'x' => 2]);
    }

    public function testNormalizesPublicPropertiesOfAnyObject(): void
    {
        $address = new AddressStub();
        $address->city = 'Paris';

        self::assertNormalizes('object{city: string}', $address, ['city' => 'Paris']);
    }

    public function testConfigurationNormalizesObjectShapeAsStdClass(): void
    {
        $repository = self::createRepository(config: new Configuration(isObjectAsArray: false));

        self::assertEquals(
            (object) ['a' => 1],
            self::getType('object{a: int}', $repository)->normalize((object) ['a' => 1], self::getContext($repository)),
        );
        self::assertSame(
            ['a' => 1],
            self::getType('array{a: int}', $repository)->normalize(['a' => 1], self::getContext($repository)),
        );
    }

    public function testNormalizationRejectsValueNotOfShape(): void
    {
        self::assertNormalizationFails(
            'array{a: int}',
            (object) ['a' => 1],
            '$: Value must be of type "array{a: int}", but object(stdClass) given',
        );
        self::assertNormalizationFails(
            'object{a: int}',
            ['a' => 1],
            '$: Value must be of type "object{a: int}", but array(1) given',
        );
        self::assertNormalizationFails('array{a: int}', [], '$.a: Required field "a" is missing');
    }
}
