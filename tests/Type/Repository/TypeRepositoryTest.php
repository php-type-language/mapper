<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Repository;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Tests\Stub\AddressStub;
use TypeLang\Mapper\Tests\Stub\BrokenOwnerStub;
use TypeLang\Mapper\Tests\Stub\CyclicChildStub;
use TypeLang\Mapper\Tests\Stub\MissingClassStub;
use TypeLang\Mapper\Tests\TestCase;
use TypeLang\Mapper\Tests\Type\Repository\Stub\CountingDecoratorStub;
use TypeLang\Mapper\Tests\Type\Repository\Stub\FixedTypeBuilderStub;
use TypeLang\Mapper\Type\StringType;

final class TypeRepositoryTest extends TestCase
{
    public function testBuildsTypeOnce(): void
    {
        $repository = self::createRepository();

        self::assertSame(self::getType('int', $repository), self::getType('int', $repository));
    }

    public function testFullyQualifiedAndQualifiedNamesAreOneType(): void
    {
        $repository = self::createRepository();

        self::assertSame(
            self::getType(AddressStub::class, $repository),
            self::getType('\\' . AddressStub::class, $repository),
        );
    }

    public function testStringLiteralWithLeadingBackslashIsOtherType(): void
    {
        $repository = self::createRepository();

        self::assertNotSame(self::getType("'\\Foo'", $repository), self::getType("'Foo'", $repository));
        self::assertDenormalizes("'Foo'", 'Foo', 'Foo', $repository);
    }

    public function testFailedTypeLeavesNoBuiltTypesReferringToIt(): void
    {
        $repository = self::createRepository();
        $message = \sprintf('Type "\\%s" is not supported', MissingClassStub::class);

        $owner = self::captureThrowable(static fn(): mixed => self::getType(BrokenOwnerStub::class, $repository));
        $child = self::captureThrowable(static fn(): mixed => self::getType(CyclicChildStub::class, $repository));

        self::assertInstanceOf(TypeDefinitionException::class, $owner);
        self::assertSame($message, $owner->getMessage());
        self::assertInstanceOf(TypeDefinitionException::class, $child);
        self::assertSame($message, $child->getMessage());
    }

    public function testFailedTypeKeepsTypesBuiltBeforeIt(): void
    {
        $repository = self::createRepository();
        $int = self::getType('int', $repository);

        self::captureThrowable(static fn(): mixed => self::getType(BrokenOwnerStub::class, $repository));

        self::assertSame($int, self::getType('int', $repository));
    }

    public function testBuilderOfUserPrecedesDefaultOnes(): void
    {
        $string = new StringType();
        $repository = self::createRepository(builders: [new FixedTypeBuilderStub('int', $string)]);

        self::assertSame($string, self::getType('int', $repository));
    }

    public function testDecoratorWrapsEachTypeOnce(): void
    {
        $decorator = new CountingDecoratorStub();
        $repository = self::createRepository(decorators: [$decorator]);

        self::getType('int', $repository);
        self::getType('int', $repository);
        self::getType('string', $repository);

        self::assertSame(['int', 'string'], $decorator->decorated);
    }

    public function testDecoratedTypeIsKept(): void
    {
        $repository = self::createRepository(decorators: [new CountingDecoratorStub()]);

        self::assertSame(self::getType('int', $repository), self::getType('int', $repository));
    }

    public function testRejectsTypeNoBuilderSupports(): void
    {
        $error = self::captureThrowable(static fn(): mixed => self::getType('unknown-type'));

        self::assertInstanceOf(TypeDefinitionException::class, $error);
        self::assertSame('Type "unknown-type" is not supported', $error->getMessage());
    }

    public function testBuildContextGivesRepositoryToBuilders(): void
    {
        $builder = new FixedTypeBuilderStub('custom', new StringType());
        $repository = self::createRepository(builders: [$builder]);

        self::getType('custom', $repository);

        self::assertInstanceOf(BuildContext::class, $builder->context);
        self::assertSame($repository, $builder->context->types);
    }
}
