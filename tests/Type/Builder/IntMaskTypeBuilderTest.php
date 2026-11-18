<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Builder;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Tests\Stub\AccessStub;
use TypeLang\Mapper\Tests\TestCase;

final class IntMaskTypeBuilderTest extends TestCase
{
    /**
     * @return iterable<non-empty-string, array{non-empty-string}>
     */
    public static function maskDataProvider(): iterable
    {
        yield 'list of values' => ['int-mask<1, 2, 4>'];
        yield 'union of values' => ['int-mask-of<1|2|4>'];
        yield 'mask of constants' => [\sprintf('int-mask-of<%s::FLAG_*>', AccessStub::class)];
        yield 'list of constants' => [\sprintf(
            'int-mask<%1$s::FLAG_READ, %1$s::FLAG_WRITE, %1$s::FLAG_EXECUTE>',
            AccessStub::class,
        )];
    }

    /**
     * @param non-empty-string $type
     */
    #[DataProvider('maskDataProvider')]
    public function testAcceptsAnyCombinationOfBitsOfMask(string $type): void
    {
        self::assertDenormalizes($type, 0, 0);
        self::assertDenormalizes($type, 5, 5);
        self::assertDenormalizes($type, '7', 7);
        self::assertNormalizes($type, 3, 3);
    }

    /**
     * @param non-empty-string $type
     */
    #[DataProvider('maskDataProvider')]
    public function testRejectsBitsOutOfMask(string $type): void
    {
        $name = 'int-mask<1, 2, 4>';

        if (\str_starts_with($type, 'int-mask-of')) {
            $name = 'int-mask-of<1|2|4>';
        }

        self::assertDenormalizationFails($type, 8, \sprintf('$: Value must be of type %s (bits of 7)', $name));
        self::assertDenormalizationFails($type, -1, \sprintf('$: Value must be of type %s (bits of 7)', $name));
    }

    /**
     * @return iterable<non-empty-string, array{non-empty-string}>
     */
    public static function invalidMaskDataProvider(): iterable
    {
        yield 'string' => ["int-mask<1, 'a'>"];
        yield 'negative' => ['int-mask<-1>'];
        yield 'type' => ['int-mask-of<int>'];
        yield 'constant of string' => [\sprintf('int-mask-of<%s::NAME>', AccessStub::class)];
        yield 'wildcard' => ['int-mask<*>'];
    }

    /**
     * @param non-empty-string $type
     */
    #[DataProvider('invalidMaskDataProvider')]
    public function testMaskOfOtherValuesIsErrorOfType(string $type): void
    {
        $error = self::captureBuildError($type);

        self::assertInstanceOf(TypeDefinitionException::class, $error);
        self::assertStringContainsString('must be made of non-negative integers', $error->getMessage());
    }

    public function testMaskOfSeveralArgumentsIsNotSupported(): void
    {
        $error = self::captureBuildError('int-mask-of<1, 2>');

        self::assertInstanceOf(TypeDefinitionException::class, $error);
        self::assertSame('Type "int-mask-of<1, 2>" is not supported', $error->getMessage());
    }
}
