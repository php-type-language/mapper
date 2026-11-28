<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use TypeLang\Mapper\Tests\Stub\AddressStub;
use TypeLang\Mapper\Tests\TestCase;

final class ListTypeTest extends TestCase
{
    public function testDenormalizesEachElement(): void
    {
        self::assertDenormalizes('list<int>', ['1', 2], [1, 2]);
    }

    public function testDenormalizesEmptyList(): void
    {
        self::assertDenormalizes('list<int>', [], []);
    }

    public function testNonEmptyListRejectsEmptyArray(): void
    {
        self::assertDenormalizationFails(
            'non-empty-list<int>',
            [],
            '$: Value must be of type non-empty-list<int> (non-empty)',
        );
    }

    public function testElementTypeOfBareListIsMixed(): void
    {
        self::assertDenormalizes('list', [1, 'x', null], [1, 'x', null]);
    }

    public function testRejectsArrayWithOtherKeys(): void
    {
        self::assertDenormalizationFails('list<int>', ['a' => 1], '$: Value must be of type list<int>, but array(1) given');
    }

    public function testRejectsArrayWithGapInKeys(): void
    {
        self::assertDenormalizationFails('list<int>', [1 => 1], '$: Value must be of type list<int>, but array(1) given');
    }

    public function testRejectsObjectOfNamedKeys(): void
    {
        self::assertDenormalizationFails(
            'list<int>',
            (object) ['a' => 1],
            '$: Value must be of type list<int>, but object given',
        );
    }

    public function testRejectsInvalidElementWithItsIndex(): void
    {
        self::assertDenormalizationFails('list<int>', [1, 'x'], '$[1]: Value must be of type int, but "x" given');
    }

    public function testDenormalizesListOfObjects(): void
    {
        $address = new AddressStub();
        $address->city = 'Paris';

        self::assertDenormalizes('list<' . AddressStub::class . '>', [['city' => 'Paris']], [$address]);
    }

    public function testRejectsInvalidFieldOfObjectInList(): void
    {
        self::assertDenormalizationFails(
            'list<' . AddressStub::class . '>',
            [['city' => 'Paris'], ['city' => []]],
            '$[1].city: Value must be of type string, but array(0) given',
        );
    }

    public function testNormalizesEachElement(): void
    {
        $address = new AddressStub();
        $address->city = 'Paris';

        self::assertNormalizes('list<' . AddressStub::class . '>', [$address], [['city' => 'Paris']]);
    }

    public function testNormalizationRejectsArrayWithOtherKeys(): void
    {
        self::assertNormalizationFails('list<int>', ['a' => 1], '$: Value must be of type list<int>, but array(1) given');
    }

    public function testNormalizationRejectsInvalidElementWithItsIndex(): void
    {
        self::assertNormalizationFails('list<int>', [1, 'x'], '$[1]: Value must be of type int, but "x" given');
    }
}
