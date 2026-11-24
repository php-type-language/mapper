<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use TypeLang\Mapper\Tests\Stub\AliasedSuitStub;
use TypeLang\Mapper\Tests\Stub\SuitStub;
use TypeLang\Mapper\Tests\TestCase;

final class UnitEnumTypeTest extends TestCase
{
    public function testDenormalizesCaseByName(): void
    {
        self::assertDenormalizes(SuitStub::class, 'Spades', SuitStub::Spades);
    }

    public function testDenormalizesCaseByAliasAndNormalizesItToName(): void
    {
        self::assertDenormalizes(AliasedSuitStub::class, 'spades', AliasedSuitStub::Spades);
        self::assertNormalizes(AliasedSuitStub::class, AliasedSuitStub::Spades, 'Spades');
    }

    public function testRejectsNameOfNoCase(): void
    {
        self::assertDenormalizationFails(
            SuitStub::class,
            'spades',
            '$: Value must be of type \'Hearts\'|\'Spades\', but "spades" given',
        );
    }

    public function testStrictContextRejectsValueThatIsNotString(): void
    {
        self::assertDenormalizationFails(
            SuitStub::class,
            42,
            '$: Value must be of type \'Hearts\'|\'Spades\', but 42 given',
            self::createMapper(strict: true),
        );
    }

    public function testNormalizesCaseToItsName(): void
    {
        self::assertNormalizes(SuitStub::class, SuitStub::Hearts, 'Hearts');
        self::assertNormalizes('mixed', SuitStub::Hearts, 'Hearts');
    }

    public function testNormalizationRejectsValueThatIsNotCase(): void
    {
        self::assertNormalizationFails(
            SuitStub::class,
            'Hearts',
            '$: Value must be of type TypeLang\Mapper\Tests\Stub\SuitStub, but "Hearts" given',
        );
    }
}
