<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Builder;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Tests\TestCase;
use TypeLang\Mapper\Type\ShapeType;

final class ShapeTypeBuilderTest extends TestCase
{
    public function testFieldsAreReadWithKeysOfPhpArray(): void
    {
        $type = self::getType("array{a: int, b?: string, '7': int, int, ...}");

        self::assertInstanceOf(ShapeType::class, $type);
        $keys = $optional = [];

        foreach ($type->fields as $field) {
            $keys[] = $field->key;
            $optional[] = $field->isOptional;
        }

        self::assertSame(['a', 'b', 7, 8], $keys);
        self::assertSame([false, true, false, false], $optional);
        self::assertNotNull($type->rest);
        self::assertFalse($type->isObject);
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
        $error = self::captureThrowable(static fn(): mixed => self::getType($type));

        self::assertInstanceOf(TypeDefinitionException::class, $error);
        self::assertSame($message, $error->getMessage());
    }
}
