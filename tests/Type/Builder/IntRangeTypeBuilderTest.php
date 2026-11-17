<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Builder;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Tests\TestCase;

final class IntRangeTypeBuilderTest extends TestCase
{
    public function testRangeOpenOnBothSidesIsInt(): void
    {
        self::assertDenormalizes('int<min, max>', \PHP_INT_MIN, \PHP_INT_MIN);
        self::assertDenormalizes('int<min, max>', \PHP_INT_MAX, \PHP_INT_MAX);
    }

    public function testBoundsAreChecked(): void
    {
        self::assertDenormalizes('int<-5, max>', -5, -5);
        self::assertDenormalizationFails('int<-5, max>', -6, '$: Value must be of type "int<-5, max>" (>= -5)');
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
        $error = self::captureBuildError($type);

        self::assertInstanceOf(TypeDefinitionException::class, $error);
        self::assertSame($message, $error->getMessage());
    }
}
