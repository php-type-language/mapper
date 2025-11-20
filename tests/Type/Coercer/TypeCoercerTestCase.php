<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Coercer;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Tests\Concerns\InteractWithRuntimeContext;
use TypeLang\Mapper\Tests\TestCase;
use TypeLang\Mapper\Tests\Type\Stub\DataSamples;

abstract class TypeCoercerTestCase extends TestCase
{
    use InteractWithRuntimeContext;

    /**
     * @return iterable<mixed, mixed>
     */
    abstract protected static function castValues(bool $normalize): iterable;

    abstract protected static function createCoercer(): TypeCoercerInterface;

    /**
     * @return iterable<mixed, \ValueError>
     */
    final protected static function defaultCoercionSamples(): iterable
    {
        return (new DataSamples())
            ->getCastsIterator();
    }

    /**
     * @return iterable<non-empty-string, array{mixed, bool}>
     */
    public static function castNormalizationDataProvider(): iterable
    {
        return self::dataProviderOf(static::castValues(true));
    }

    /**
     * @api
     */
    #[DataProvider('castNormalizationDataProvider')]
    public function testCoerceForNormalization(mixed $value, mixed $expected): void
    {
        $this->expectTypeErrorIfException($expected);

        $coercer = static::createCoercer();

        $actual = $coercer->tryCoerce($value, self::createNormalizationContext(
            value: $value,
        ));

        self::assertIfNotException($expected, $actual);
    }

    /**
     * @return iterable<non-empty-string, array{mixed, bool}>
     */
    public static function castDenormalizationDataProvider(): iterable
    {
        return self::dataProviderOf(static::castValues(false));
    }

    /**
     * @api
     */
    #[DataProvider('castDenormalizationDataProvider')]
    public function testCoerceForDenormalization(mixed $value, mixed $expected): void
    {
        $this->expectTypeErrorIfException($expected);

        $coercer = static::createCoercer();

        $actual = $coercer->tryCoerce($value, self::createNormalizationContext(
            value: $value,
        ));

        self::assertIfNotException($expected, $actual);
    }
}
