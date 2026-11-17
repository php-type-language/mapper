<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Builder;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Tests\Stub\ConstantsStub;
use TypeLang\Mapper\Tests\Stub\StatusStub;
use TypeLang\Mapper\Tests\TestCase;

final class ConstantTypeBuilderTest extends TestCase
{
    private const STUB = ConstantsStub::class;

    /**
     * @return iterable<non-empty-string, array{non-empty-string, mixed, mixed}>
     */
    public static function acceptedDataProvider(): iterable
    {
        yield 'string constant' => [self::STUB . '::METHOD_GET', 'GET', 'GET'];
        yield 'coerced int constant' => [self::STUB . '::CODE_OK', '200', 200];
        yield 'null constant' => [self::STUB . '::NOTHING', null, null];
        yield 'enum case constant' => [self::STUB . '::STATUS', 'active', StatusStub::Active];
        yield 'enum case' => [StatusStub::class . '::Active', 'active', StatusStub::Active];
        yield 'mask by prefix' => [self::STUB . '::METHOD_*', 'POST', 'POST'];
        yield 'mask by suffix' => [self::STUB . '::*_OK', 200, 200];
        yield 'mask of enum' => [StatusStub::class . '::*', 'banned', StatusStub::Banned];
        yield 'mask of global constants' => ['E_USER_*', \E_USER_ERROR, \E_USER_ERROR];
    }

    /**
     * @param non-empty-string $type
     */
    #[DataProvider('acceptedDataProvider')]
    public function testDenormalizesValueOfConstant(string $type, mixed $input, mixed $expected): void
    {
        self::assertDenormalizes($type, $input, $expected);
    }

    public function testRejectsValueOfOtherConstant(): void
    {
        self::assertDenormalizationFails(
            self::STUB . '::METHOD_GET',
            'POST',
            \sprintf('$: Value must be of type "%s::METHOD_GET", but "POST" given', self::STUB),
        );
        self::assertDenormalizationFails(
            self::STUB . '::METHOD_*',
            'PUT',
            \sprintf('$: Value must be of type "%s::METHOD_*", but "PUT" given', self::STUB),
        );
    }

    public function testMaskOfNamespaceMatchesConstantsDefinedInIt(): void
    {
        if (!\defined('TypeLang\Mapper\Tests\Stub\LEVEL_LOW')) {
            \define('TypeLang\Mapper\Tests\Stub\LEVEL_LOW', 1);
            \define('TypeLang\Mapper\Tests\Stub\LEVEL_HIGH', 2);
        }

        self::assertDenormalizes('TypeLang\Mapper\Tests\Stub\LEVEL_*', 2, 2);
        self::assertDenormalizationFails(
            'TypeLang\Mapper\Tests\Stub\LEVEL_*',
            3,
            '$: Value must be of type "TypeLang\Mapper\Tests\Stub\LEVEL_*", but 3 given',
        );
    }

    public function testCaseOfEnumIsNormalizedToItsValue(): void
    {
        self::assertNormalizes(self::STUB . '::STATUS', StatusStub::Active, 'active');
        self::assertNormalizes(self::STUB . '::CODE_*', 201, 201);
    }

    /**
     * @return iterable<non-empty-string, array{
     *     non-empty-string,
     *     non-empty-string,
     * }>
     */
    public static function invalidDataProvider(): iterable
    {
        yield 'unknown constant' => [self::STUB . '::NOPE', 'Type "%s" refers to no constant'];
        yield 'mask matching nothing' => [self::STUB . '::NOPE_*', 'Type "%s" refers to no constant'];
        yield 'unknown class' => ['Missing::NOPE', 'Type "%s" refers to no constant'];
        yield 'array constant' => [
            self::STUB . '::LIST',
            'Type "%s" refers to a constant of type "array", only a scalar, "null" or a case of an enum is allowed',
        ];
        yield 'class relative to declaration' => [
            'self::LIST',
            'Type "%s" refers to a class relative to the declaration, write the class by its name',
        ];
    }

    /**
     * @param non-empty-string $type
     * @param non-empty-string $message
     */
    #[DataProvider('invalidDataProvider')]
    public function testInvalidConstantIsRejected(string $type, string $message): void
    {
        $error = self::captureThrowable(static fn(): mixed => self::getType($type));

        self::assertInstanceOf(TypeDefinitionException::class, $error);
        self::assertSame(\sprintf($message, $type), $error->getMessage());
    }
}
