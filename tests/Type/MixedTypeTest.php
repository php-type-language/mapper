<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Tests\Stub\AddressStub;
use TypeLang\Mapper\Tests\Stub\StatusStub;
use TypeLang\Mapper\Tests\TestCase;

final class MixedTypeTest extends TestCase
{
    /**
     * @return iterable<non-empty-string, array{mixed}>
     */
    public static function anyDataProvider(): iterable
    {
        yield 'null' => [null];
        yield 'integer' => [42];
        yield 'string' => ['x'];
        yield 'array' => [['a' => [1, 2]]];
        yield 'object' => [(object) ['a' => 1]];
    }

    #[DataProvider('anyDataProvider')]
    public function testDenormalizesAnyValueAsIs(mixed $input): void
    {
        self::assertDenormalizes('mixed', $input, $input);
    }

    public function testNormalizesScalarsAndArraysAsIs(): void
    {
        self::assertNormalizes('mixed', ['a' => [1, 'x', null, true]], ['a' => [1, 'x', null, true]]);
    }

    public function testNormalizesObjectByTypeOfItsClass(): void
    {
        $address = new AddressStub();
        $address->city = 'Paris';

        self::assertNormalizes('mixed', ['home' => $address, 'status' => StatusStub::Active], [
            'home' => ['city' => 'Paris'],
            'status' => 'active',
        ]);
    }

    public function testNormalizesStdClassAsArrayOfItsProperties(): void
    {
        self::assertNormalizes('mixed', (object) ['a' => 1, 'b' => (object) ['c' => 2]], ['a' => 1, 'b' => ['c' => 2]]);
    }

    public function testNormalizationRejectsInvalidObjectWithItsPath(): void
    {
        $address = new AddressStub();

        self::assertNormalizationFails(
            'mixed',
            ['items' => [$address]],
            '$.items[0].city: Value cannot be read',
        );
    }

    public function testObjectOfUnsupportedClassIsErrorOfTypeInCheckAndConversion(): void
    {
        $mapper = self::createMapper();
        $value = ['items' => [static fn(): int => 1]];

        $check = self::captureThrowable(static fn(): bool => $mapper->isNormalizable($value, 'mixed'));
        $conversion = self::captureThrowable(static fn(): mixed => $mapper->normalize($value, 'mixed'));

        self::assertInstanceOf(TypeDefinitionException::class, $check);
        self::assertInstanceOf(TypeDefinitionException::class, $conversion);
        self::assertSame('Type "Closure" is not supported', $check->getMessage());
        self::assertSame($check->getMessage(), $conversion->getMessage());
    }


    public function testDynamicObjectStaysObjectInCaseOfObjectsAreNotArrays(): void
    {
        $mapper = self::createMapper(config: new Configuration(normalizeAsArray: false));

        self::assertEquals(
            (object) ['a' => (object) []],
            $mapper->normalize((object) ['a' => new \stdClass()], 'mixed'),
        );
    }
}
