<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use TypeLang\Mapper\Tests\Stub\AddressStub;
use TypeLang\Mapper\Tests\TestCase;

final class ObjectTypeTest extends TestCase
{
    public function testDenormalizesDataIntoStdClass(): void
    {
        self::assertDenormalizes('object', ['a' => 1], (object) ['a' => 1]);
        self::assertDenormalizes('\stdClass', ['a' => ['b' => 1]], (object) ['a' => ['b' => 1]]);
        self::assertDenormalizes('stdClass', (object) ['a' => 1], (object) ['a' => 1]);
    }

    public function testRejectsValueThatIsNotData(): void
    {
        self::assertDenormalizationFails('object', 'x', '$: Value must be of type "object", but "x" given');
    }

    public function testNormalizesObjectByTypeOfItsClass(): void
    {
        $address = new AddressStub();
        $address->city = 'Paris';

        self::assertNormalizes('object', $address, ['city' => 'Paris']);
        self::assertNormalizes('object', (object) ['a' => (object) ['b' => 1]], ['a' => ['b' => 1]]);
    }

    public function testNormalizesPropertiesOfStdClass(): void
    {
        self::assertNormalizes('stdClass', (object) ['a' => 1, 'b' => [2]], ['a' => 1, 'b' => [2]]);
        self::assertNormalizes('mixed', (object) ['a' => 1], ['a' => 1]);
    }

    public function testNormalizationRejectsValueThatIsNotObject(): void
    {
        self::assertNormalizationFails('object', ['a' => 1], '$: Value must be of type "object", but array(1) given');
    }
}
