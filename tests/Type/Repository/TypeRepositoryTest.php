<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Repository;

use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Tests\Stub\AddressStub;
use TypeLang\Mapper\Tests\Stub\BrokenOwnerStub;
use TypeLang\Mapper\Tests\Stub\CyclicChildStub;
use TypeLang\Mapper\Tests\Stub\MissingClassStub;
use TypeLang\Mapper\Tests\TestCase;
use TypeLang\Mapper\Tests\Type\Repository\Stub\CountingDecoratorStub;
use TypeLang\Mapper\Tests\Type\Repository\Stub\FixedTypeBuilderStub;
use TypeLang\Mapper\Type\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\StringType;

final class TypeRepositoryTest extends TestCase
{
    public function testBuildsTypeOnce(): void
    {
        $decorator = new CountingDecoratorStub();
        $mapper = self::createMapper(decorators: [$decorator]);

        $mapper->denormalize(1, 'int');
        $mapper->denormalize(2, 'int');
        $mapper->denormalize('a', 'string');

        self::assertSame(['int', 'string'], $decorator->decorated);
    }

    public function testFullyQualifiedAndQualifiedNamesAreOneType(): void
    {
        $decorator = new CountingDecoratorStub();
        $mapper = self::createMapper(decorators: [$decorator]);

        $mapper->isDenormalizable([], AddressStub::class);
        $mapper->isDenormalizable([], '\\' . AddressStub::class);

        self::assertCount(1, \array_keys($decorator->decorated, AddressStub::class, true));
    }

    public function testStringLiteralWithLeadingBackslashIsOtherType(): void
    {
        $mapper = self::createMapper();

        self::assertFalse($mapper->isDenormalizable('Foo', "'\\Foo'"));
        self::assertDenormalizes("'Foo'", 'Foo', 'Foo', $mapper);
    }

    public function testFailedTypeLeavesNoBuiltTypesReferringToIt(): void
    {
        $mapper = self::createMapper();
        $message = \sprintf('Type "\\%s" is not supported', MissingClassStub::class);

        $owner = self::captureBuildError(BrokenOwnerStub::class, $mapper);
        $child = self::captureBuildError(CyclicChildStub::class, $mapper);

        self::assertInstanceOf(TypeDefinitionException::class, $owner);
        self::assertSame($message, $owner->getMessage());
        self::assertInstanceOf(TypeDefinitionException::class, $child);
        self::assertSame($message, $child->getMessage());
    }

    public function testFailedTypeKeepsTypesBuiltBeforeIt(): void
    {
        $decorator = new CountingDecoratorStub();
        $mapper = self::createMapper(decorators: [$decorator]);

        $mapper->denormalize(1, 'int');
        self::captureBuildError(BrokenOwnerStub::class, $mapper);
        $mapper->denormalize(2, 'int');

        self::assertCount(1, \array_keys($decorator->decorated, 'int', true));
    }

    public function testBuilderOfUserPrecedesDefaultOnes(): void
    {
        $mapper = self::createMapper(builders: [new FixedTypeBuilderStub('int', new StringType())]);

        self::assertSame('abc', $mapper->denormalize('abc', 'int'));
    }

    public function testRejectsTypeNoBuilderSupports(): void
    {
        $error = self::captureBuildError('unknown-type');

        self::assertInstanceOf(TypeDefinitionException::class, $error);
        self::assertSame('Type "unknown-type" is not supported', $error->getMessage());
    }

    public function testBuildContextGivesRepositoryToBuilders(): void
    {
        $builder = new FixedTypeBuilderStub('custom', new StringType());

        self::createMapper(builders: [$builder])->denormalize('a', 'custom');

        self::assertInstanceOf(TypeRepositoryInterface::class, $builder->context?->types);
    }
}
