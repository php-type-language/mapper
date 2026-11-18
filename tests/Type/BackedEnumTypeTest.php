<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use TypeLang\Mapper\Tests\Stub\AliasPriorityStub;
use TypeLang\Mapper\Tests\Stub\AliasStatusStub;
use TypeLang\Mapper\Tests\Stub\LevelStub;
use TypeLang\Mapper\Tests\Stub\StatusStub;
use TypeLang\Mapper\Tests\TestCase;

final class BackedEnumTypeTest extends TestCase
{
    public function testDenormalizesCaseByValue(): void
    {
        self::assertDenormalizes(StatusStub::class, 'banned', StatusStub::Banned);
    }

    public function testRejectsValueOfNoCase(): void
    {
        self::assertDenormalizationFails(
            StatusStub::class,
            'deleted',
            '$: Value must be of type \'active\'|\'banned\', but "deleted" given',
        );
    }

    public function testCoercesValueByBackingType(): void
    {
        self::assertDenormalizes(LevelStub::class, '2', LevelStub::High);
    }

    public function testStrictContextRejectsValueOfOtherBackingType(): void
    {
        self::assertDenormalizationFails(
            LevelStub::class,
            '2',
            '$: Value must be of type 1|2, but "2" given',
            self::createMapper(strict: true),
        );
    }

    public function testNormalizesCaseToItsValue(): void
    {
        self::assertNormalizes(StatusStub::class, StatusStub::Active, 'active');
        self::assertNormalizes(LevelStub::class, LevelStub::Low, 1);
    }

    public function testDenormalizesCaseByAliasInAnyMode(): void
    {
        self::assertDenormalizes(AliasStatusStub::class, 'on', AliasStatusStub::Enabled);
        self::assertDenormalizes(AliasStatusStub::class, 'active', AliasStatusStub::Enabled);
        self::assertDenormalizes(AliasStatusStub::class, 'enabled', AliasStatusStub::Enabled);
        self::assertDenormalizes(
            AliasStatusStub::class,
            'on',
            AliasStatusStub::Enabled,
            self::createMapper(strict: true),
        );
    }

    public function testAliasIsMatchedAfterBackingType(): void
    {
        self::assertDenormalizes(AliasPriorityStub::class, '10', AliasPriorityStub::High);
        self::assertDenormalizationFails(
            AliasPriorityStub::class,
            '10',
            '$: Value must be of type 1|2, but "10" given',
            self::createMapper(strict: true),
        );
    }

    public function testCaseWithAliasesIsNormalizedToItsValue(): void
    {
        self::assertNormalizes(AliasStatusStub::class, AliasStatusStub::Enabled, 'enabled');
    }

    public function testValueThatIsNeitherValueNorAliasIsRejected(): void
    {
        self::assertDenormalizationFails(
            AliasStatusStub::class,
            'off',
            '$: Value must be of type \'enabled\'|\'disabled\', but "off" given',
        );
    }

    public function testNormalizationRejectsValueThatIsNotCase(): void
    {
        self::assertNormalizationFails(
            StatusStub::class,
            'active',
            '$: Value must be of type TypeLang\Mapper\Tests\Stub\StatusStub, but "active" given',
        );
    }
}
