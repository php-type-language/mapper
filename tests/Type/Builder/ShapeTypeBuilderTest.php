<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Builder;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Tests\TestCase;

final class ShapeTypeBuilderTest extends TestCase
{
    public function testFieldsAreReadWithKeysOfPhpArray(): void
    {
        $type = "array{a: int, b?: string, '7': int, int, ...}";

        self::assertDenormalizes($type, ['a' => 1, 7 => 2, 8 => 3, 'x' => 4], ['a' => 1, 7 => 2, 8 => 3, 'x' => 4]);
        self::assertDenormalizationFails($type, ['a' => 1, 7 => 2], '$[8]: Required field "8" is missing');
    }

    /**
     * @return iterable<non-empty-string, array{
     *     non-empty-string,
     *     non-empty-string,
     * }>
     */
    public static function invalidDataProvider(): iterable
    {
        yield 'implicit key after the highest one' => [
            'array{9223372036854775807: int, string}',
            'Type "array{9223372036854775807: int, string}" declares the key "9223372036854775807" more than once',
        ];
        yield 'repeated key' => [
            'array{a: int, a: string}',
            'Type "array{a: int, a: string}" declares the key "a" more than once',
        ];
        yield 'implicit key repeating literal one' => [
            'array{0: int, int, 1: int}',
            'Type "array{0: int, int, 1: int}" declares the key "1" more than once',
        ];
        yield 'empty literal key' => [
            "array{'': int}",
            "Type \"array{'': int}\" has a key that is not a name or a non-empty literal",
        ];
    }

    /**
     * @param non-empty-string $type
     * @param non-empty-string $message
     */
    #[DataProvider('invalidDataProvider')]
    public function testInvalidShapeIsRejected(string $type, string $message): void
    {
        $error = self::captureBuildError($type);

        self::assertInstanceOf(TypeDefinitionException::class, $error);
        self::assertSame($message, $error->getMessage());
    }
}
