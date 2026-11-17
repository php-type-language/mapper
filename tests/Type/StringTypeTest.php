<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Tests\TestCase;

final class StringTypeTest extends TestCase
{
    /**
     * @return iterable<non-empty-string, array{mixed, string}>
     */
    public static function denormalizableDataProvider(): iterable
    {
        yield 'string' => ['x', 'x'];
        yield 'empty string' => ['', ''];
        yield 'integer' => [42, '42'];
        yield 'float' => [4.5, '4.5'];
    }

    /**
     * @return iterable<non-empty-string, array{mixed, non-empty-string}>
     */
    public static function nonDenormalizableDataProvider(): iterable
    {
        yield 'boolean' => [true, '$: Value must be of type "string", but true given'];
        yield 'null' => [null, '$: Value must be of type "string", but null given'];
        yield 'array' => [[], '$: Value must be of type "string", but array(0) given'];
    }

    #[DataProvider('denormalizableDataProvider')]
    public function testDenormalizesStringAndNumbers(mixed $input, string $expected): void
    {
        self::assertDenormalizes('string', $input, $expected);
    }

    /**
     * @param non-empty-string $message
     */
    #[DataProvider('nonDenormalizableDataProvider')]
    public function testRejectsValueThatIsNotStringForm(mixed $input, string $message): void
    {
        self::assertDenormalizationFails('string', $input, $message);
    }

    public function testStrictContextRejectsNumber(): void
    {
        self::assertDenormalizationFails(
            'string',
            42,
            '$: Value must be of type "string", but 42 given',
            self::createRepository(isStrict: true),
        );
    }

    public function testNormalizesStringAsIs(): void
    {
        self::assertNormalizes('string', 'x', 'x');
    }

    public function testNormalizationDoesNotCoerce(): void
    {
        self::assertNormalizationFails('string', 42, '$: Value must be of type "string", but 42 given');
    }
}
