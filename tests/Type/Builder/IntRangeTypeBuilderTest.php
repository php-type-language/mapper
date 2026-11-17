<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Builder;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Tests\TestCase;
use TypeLang\Mapper\Type\Check\IntRangeCheck;
use TypeLang\Mapper\Type\IntType;
use TypeLang\Mapper\Type\RefinedType;

final class IntRangeTypeBuilderTest extends TestCase
{
    public function testRangeOpenOnBothSidesIsInt(): void
    {
        self::assertInstanceOf(IntType::class, self::getType('int<min, max>'));
    }

    public function testBoundsAreReadIntoCheck(): void
    {
        $type = self::getType('int<-5, max>');

        self::assertInstanceOf(RefinedType::class, $type);
        self::assertEquals([new IntRangeCheck(min: -5)], $type->checks);
        self::assertSame('int<-5, max>', $type->name);
    }

    /**
     * @return iterable<non-empty-string, array{
     *     non-empty-string,
     *     non-empty-string,
     * }>
     */
    public static function invalidDataProvider(): iterable
    {
        yield 'float bound' => [
            'int<1.5, 2>',
            'Template argument #1 of type "int<1.5, 2>" is invalid: must be an integer or "min"',
        ];
        yield 'string bound' => [
            "int<1, 'x'>",
            'Template argument #2 of type "int<1, \'x\'>" is invalid: must be an integer or "max"',
        ];
        yield 'swapped open bounds' => [
            'int<max, 1>',
            'Template argument #1 of type "int<max, 1>" is invalid: must be an integer or "min"',
        ];
        yield 'empty range' => [
            'int<10, 1>',
            'Type "int<10, 1>" is empty: the lower bound is greater than the upper one',
        ];
    }

    /**
     * @param non-empty-string $type
     * @param non-empty-string $message
     */
    #[DataProvider('invalidDataProvider')]
    public function testInvalidRangeIsRejected(string $type, string $message): void
    {
        $error = self::captureThrowable(static fn(): mixed => self::getType($type));

        self::assertInstanceOf(TypeDefinitionException::class, $error);
        self::assertSame($message, $error->getMessage());
    }
}
