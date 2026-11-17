<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use TypeLang\Mapper\Tests\Stub\SuitStub;
use TypeLang\Mapper\Tests\TestCase;

final class UnitEnumTypeTest extends TestCase
{
    public function testDenormalizesCaseByName(): void
    {
        self::assertDenormalizes(SuitStub::class, 'Spades', SuitStub::Spades);
    }

    public function testRejectsNameOfNoCase(): void
    {
        self::assertDenormalizationFails(
            SuitStub::class,
            'spades',
            \sprintf('$: Value must be of type "%s", but "spades" given', SuitStub::class),
        );
    }

    public function testStrictContextRejectsValueThatIsNotString(): void
    {
        self::assertDenormalizationFails(
            SuitStub::class,
            42,
            '$: Value must be of type "string", but 42 given',
            self::createRepository(isStrict: true),
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
            \sprintf('$: Value must be of type "%s", but "Hearts" given', SuitStub::class),
        );
    }
}
