<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Tests\TestCase;

final class FloatTypeTest extends TestCase
{
    /**
     * @return iterable<non-empty-string, array{mixed, float}>
     */
    public static function denormalizableDataProvider(): iterable
    {
        yield 'float' => [4.5, 4.5];
        yield 'infinity' => [\INF, \INF];
        yield 'integer' => [42, 42.0];
        yield 'numeric string' => ['4.5', 4.5];
        yield 'exponent string' => ['1e3', 1000.0];
    }

    /**
     * @return iterable<non-empty-string, array{mixed, non-empty-string}>
     */
    public static function nonDenormalizableDataProvider(): iterable
    {
        yield 'integer losing precision' => [
            9_007_199_254_740_993,
            '$: Value must be of type "float", but 9007199254740993 given',
        ];
        yield 'padded string' => [' 4.5', '$: Value must be of type "float", but " 4.5" given'];
        yield 'boolean' => [true, '$: Value must be of type "float", but true given'];
        yield 'null' => [null, '$: Value must be of type "float", but null given'];
    }

    #[DataProvider('denormalizableDataProvider')]
    public function testDenormalizesFloatAndItsForms(mixed $input, float $expected): void
    {
        self::assertDenormalizes('float', $input, $expected);
    }

    /**
     * @param non-empty-string $message
     */
    #[DataProvider('nonDenormalizableDataProvider')]
    public function testRejectsValueThatIsNotFloatForm(mixed $input, string $message): void
    {
        self::assertDenormalizationFails('float', $input, $message);
    }

    public function testStrictContextAcceptsExactInteger(): void
    {
        self::assertDenormalizes('float', 42, 42.0, self::createMapper(isStrict: true));
    }

    public function testStrictContextRejectsNumericString(): void
    {
        self::assertDenormalizationFails(
            'float',
            '4.5',
            '$: Value must be of type "float", but "4.5" given',
            self::createMapper(isStrict: true),
        );
    }

    public function testDenormalizesNotNumber(): void
    {
        $result = self::createMapper()->denormalize(\NAN, 'float');

        self::assertNan($result);
    }

    public function testDoubleIsAliasOfFloat(): void
    {
        self::assertDenormalizes('double', '4.5', 4.5);
    }

    public function testNormalizesFloatAsIs(): void
    {
        self::assertNormalizes('float', 4.5, 4.5);
        self::assertNormalizationFails('float', 4, '$: Value must be of type "float", but 4 given');
    }
}
