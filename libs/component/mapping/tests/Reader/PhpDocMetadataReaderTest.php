<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Reader;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Reader\PhpDocMetadataReader;
use TypeLang\Mapper\Mapping\Tests\Stub\BrokenDocBlockStub;
use TypeLang\Mapper\Mapping\Tests\Stub\BrokenPromotedStub;
use TypeLang\Mapper\Mapping\Tests\Stub\DocBlockStub;
use TypeLang\Mapper\Mapping\Tests\Stub\OrphanParentStub;
use TypeLang\Mapper\Mapping\Tests\Stub\PromotedStub;
use TypeLang\Mapper\Mapping\Tests\Stub\SelfBaseStub;
use TypeLang\Mapper\Mapping\Tests\Stub\SelfChildStub;
use TypeLang\Mapper\Mapping\Tests\Stub\TraitUserStub;
use TypeLang\Mapper\Mapping\Tests\Stub\UserStub;
use TypeLang\Mapper\Mapping\Tests\TestCase;

final class PhpDocMetadataReaderTest extends TestCase
{
    public function testFirstReaderReturnsNoProperties(): void
    {
        $metadata = self::readAsFirst(new PhpDocMetadataReader(), DocBlockStub::class);

        self::assertSame(DocBlockStub::class, $metadata->name);
        self::assertSame([], $metadata->properties);
    }

    public function testVarTagIsResolvedByImportsOfClassFile(): void
    {
        $metadata = self::readPhpDoc(DocBlockStub::class);

        $accounts = self::getProperty($metadata, 'accounts');
        self::assertType('list<' . UserStub::class . '>', $accounts->read);
        self::assertType('list<' . UserStub::class . '>', $accounts->write);
        self::assertSame('DocBlockStub.php', \basename((string) $accounts->read->source->file));
    }

    public function testNamesWithoutImportAreKeptAsWritten(): void
    {
        $metadata = self::readPhpDoc(DocBlockStub::class);

        self::assertType('T', self::getProperty($metadata, 'template')->read);
        self::assertType(DocBlockStub::class . '<T>|null', self::getProperty($metadata, 'self')->read);
    }

    public function testSelfIsDeclaringClassAndStaticIsReadClass(): void
    {
        $metadata = self::readPhpDoc(SelfChildStub::class);

        self::assertType(SelfBaseStub::class . '|null', self::getProperty($metadata, 'self')->read);
        self::assertType(SelfChildStub::class . '|null', self::getProperty($metadata, 'static')->read);
        self::assertType(SelfBaseStub::class . '|null', self::getProperty($metadata, 'parent')->read);
    }

    public function testParentOfClassWithoutParentIsErrorOfType(): void
    {
        $type = self::getProperty(self::readPhpDoc(OrphanParentStub::class), 'parent')->read;

        self::assertNotNull($type);
        self::assertNull($type->type);
        self::assertSame(
            \sprintf('class "%s" has no parent, "parent" cannot be resolved', OrphanParentStub::class),
            $type->error,
        );
    }

    public function testClassOfNamespaceOfDeclarationIsResolvedWithoutImport(): void
    {
        $metadata = self::readPhpDoc(PromotedStub::class);

        self::assertType('list<' . UserStub::class . '>', self::getProperty($metadata, 'users')->write);
    }

    public function testTagUnknownToParserIsIgnoredAndTagOfOtherVariableIsSkipped(): void
    {
        $metadata = self::readPhpDoc(DocBlockStub::class);

        self::assertType('array', self::getProperty($metadata, 'prefixed')->read);
        self::assertType('mixed', self::getProperty($metadata, 'foreign')->read);
    }

    public function testFirstTagOfKindWinsAmongAliases(): void
    {
        $reader = self::createPhpDocReader(['phpstan-var' => 'var']);

        $metadata = self::readOverReflection($reader, DocBlockStub::class);

        self::assertType('array', self::getProperty($metadata, 'prefixed')->read);
    }

    public function testAliasOfParserPlatformIsReadAsItsTag(): void
    {
        $reader = self::createPhpDocReader(['type-lang-var' => 'var']);

        $metadata = self::readOverReflection($reader, BrokenDocBlockStub::class);

        self::assertType('positive-int', self::getProperty($metadata, 'value')->read);
    }

    public function testPromotedPropertyIsReadFromVarThenFromConstructorParam(): void
    {
        $metadata = self::readPhpDoc(PromotedStub::class);

        self::assertType('list<' . UserStub::class . '>', self::getProperty($metadata, 'users')->write);
        self::assertType('positive-int', self::getProperty($metadata, 'count')->read);
        self::assertType('TItem', self::getProperty($metadata, 'item')->read);
        self::assertType('string', self::getProperty($metadata, 'required')->read);
    }

    public function testUnparsableConstructorParamIsNotRead(): void
    {
        $metadata = self::readPhpDoc(BrokenPromotedStub::class);

        self::assertType('array', self::getProperty($metadata, 'users')->read);
        self::assertType('?int', self::getProperty($metadata, 'nullable')->read);
        self::assertType('positive-int', self::getProperty($metadata, 'count')->read);
        self::assertType('non-empty-string', self::getProperty($metadata, 'name')->read);
    }

    public function testTraitPropertyIsResolvedByImportsOfTraitFile(): void
    {
        $type = self::getProperty(self::readPhpDoc(TraitUserStub::class), 'owners')->read;

        self::assertType('list<' . UserStub::class . '>', $type);
        self::assertSame('OwnerTrait.php', \basename((string) $type->source->file));
    }

    public function testHigherPhpDocReaderOverridesLowerOne(): void
    {
        $class = new \ReflectionClass(BrokenDocBlockStub::class);
        $lower = self::readOverReflection(self::createPhpDocReader(['broken-var' => 'var']), $class->name);

        $metadata = self::createPhpDocReader(['type-lang-var' => 'var'])->read($class, $lower);

        self::assertType('int', self::getProperty($lower, 'value')->read);
        self::assertType('positive-int', self::getProperty($metadata, 'value')->read);
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return ClassMetadata<T>
     * @throws \Throwable
     */
    private static function readPhpDoc(string $class): ClassMetadata
    {
        return self::readOverReflection(new PhpDocMetadataReader(), $class);
    }
}
