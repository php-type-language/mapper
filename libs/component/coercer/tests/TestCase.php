<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Coercer\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase as BaseTestCase;
use TypeLang\Mapper\Coercer\CoercerInterface;
use TypeLang\Mapper\Coercer\Tests\Stub\IntBackedEnumStub;
use TypeLang\Mapper\Coercer\Tests\Stub\StringableStub;
use TypeLang\Mapper\Coercer\Tests\Stub\StringBackedEnumStub;
use TypeLang\Mapper\Coercer\Tests\Stub\UnitEnumStub;

#[Group('unit'), Group('type-lang/mapper')]
abstract class TestCase extends BaseTestCase
{
    /**
     * @return CoercerInterface<int|float|string|bool|array<array-key, mixed>>
     */
    abstract protected static function createCoercer(): CoercerInterface;

    /**
     * Values with the expected result, including values of the target type
     *
     * @return iterable<non-empty-string, array{mixed, mixed}>
     */
    abstract public static function coercibleValueDataProvider(): iterable;

    /**
     * Values of the coerced kinds that cannot be converted without loss
     *
     * @return iterable<non-empty-string, array{mixed}>
     */
    abstract public static function rejectedValueDataProvider(): iterable;

    /**
     * Values that no coercer converts
     *
     * @return iterable<non-empty-string, array{mixed}>
     */
    public static function foreignValueDataProvider(): iterable
    {
        yield 'null' => [null];
        yield 'stringable object' => [new StringableStub()];
        yield 'int backed enum case' => [IntBackedEnumStub::Example];
        yield 'string backed enum case' => [StringBackedEnumStub::Example];
        yield 'unit enum case' => [UnitEnumStub::Example];
        yield 'date time object' => [new \DateTimeImmutable()];
        yield 'array object' => [new \ArrayObject([1])];
        yield 'generator' => [(static function (): \Generator {
            yield 1;
        })()];
        yield 'resource' => [\fopen('php://memory', 'rb')];

        $closed = \fopen('php://memory', 'rb');
        \assert($closed !== false);
        \fclose($closed);

        yield 'closed resource' => [$closed];
    }

    #[DataProvider('coercibleValueDataProvider')]
    public function testConvertsValueWithoutLoss(mixed $value, mixed $expected): void
    {
        self::assertSameValue($expected, static::createCoercer()->coerce($value));
    }

    #[DataProvider('rejectedValueDataProvider')]
    public function testReturnsNullForValueThatCannotBeConverted(mixed $value): void
    {
        self::assertNull(static::createCoercer()->coerce($value));
    }

    #[DataProvider('foreignValueDataProvider')]
    public function testReturnsNullForForeignValue(mixed $value): void
    {
        self::assertNull(static::createCoercer()->coerce($value));
    }

    /**
     * Asserts that values are identical, telling `42` from `42.0` and `0.0`
     * from `-0.0`, and matching `NAN`.
     */
    protected static function assertSameValue(mixed $expected, mixed $actual): void
    {
        self::assertSame(\var_export($expected, true), \var_export($actual, true));
    }
}
