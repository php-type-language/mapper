<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Tests\Concerns\InteractWithContext;
use TypeLang\Mapper\Tests\TestCase;
use TypeLang\Mapper\Tests\Type\Stub\DataSamples;
use TypeLang\Mapper\Type\TypeInterface;

abstract class TypeTestCase extends TestCase
{
    use InteractWithContext;

    /**
     * @return iterable<mixed, bool>
     */
    abstract protected static function matchValues(bool $normalize): iterable;

    /**
     * @return iterable<mixed, mixed>
     */
    abstract protected static function castValues(bool $normalize): iterable;

    /**
     * @return iterable<mixed, bool>
     */
    final protected static function defaultMatchDataProviderSamples(): iterable
    {
        return (new DataSamples())
            ->getMatchesIterator();
    }

    /**
     * @return iterable<mixed, \Throwable>
     */
    final protected static function defaultCastDataProviderSamples(): iterable
    {
        return (new DataSamples())
            ->getCastsIterator();
    }

    /**
     * @return TypeInterface<mixed>
     */
    abstract protected static function createType(): TypeInterface;

    /**
     * @return iterable<non-empty-string, array{mixed, bool}>
     */
    public static function matchNormalizationDataProvider(): iterable
    {
        return self::dataProviderOf(static::matchValues(true));
    }

    /**
     * @api
     */
    #[DataProvider('matchNormalizationDataProvider')]
    public function testMatchNormalization(mixed $value, bool $expected): void
    {
        $type = static::createType();

        $actual = $type->match($value, self::createNormalizationContext(
            value: $value,
        ));

        self::assertSame($expected, $actual);
    }

    /**
     * @return iterable<non-empty-string, array{mixed, bool}>
     */
    public static function matchDenormalizationDataProvider(): iterable
    {
        return self::dataProviderOf(static::matchValues(false));
    }

    /**
     * @api
     */
    #[DataProvider('matchDenormalizationDataProvider')]
    public function testMatchDenormalization(mixed $value, bool $expected): void
    {
        $type = static::createType();

        $actual = $type->match($value, self::createDenormalizationContext(
            value: $value,
        ));

        self::assertSame($expected, $actual);
    }

    /**
     * @return iterable<non-empty-string, array{mixed, mixed|\Throwable}>
     */
    public static function castNormalizationDataProvider(): iterable
    {
        return self::dataProviderOf(static::castValues(true));
    }

    /**
     * @api
     */
    #[DataProvider('castNormalizationDataProvider')]
    public function testCastNormalization(mixed $value, mixed $expected): void
    {
        $this->expectTypeErrorIfException($expected);

        $type = static::createType();

        $actual = $type->cast($value, self::createNormalizationContext(
            value: $value,
        ));

        self::assertIfNotException($expected, $actual);
    }

    /**
     * @return iterable<non-empty-string, array{mixed, mixed|\Throwable}>
     */
    public static function castDenormalizationDataProvider(): iterable
    {
        return self::dataProviderOf(static::castValues(false));
    }

    /**
     * @api
     */
    #[DataProvider('castDenormalizationDataProvider')]
    public function testCastDenormalization(mixed $value, mixed $expected): void
    {
        $this->expectTypeErrorIfException($expected);

        $type = static::createType();

        $actual = $type->cast($value, self::createDenormalizationContext(
            value: $value,
        ));

        self::assertIfNotException($expected, $actual);
    }
}
