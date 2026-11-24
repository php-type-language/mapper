<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Reader;

use PHPUnit\Framework\Attributes\RequiresPhp;
use TypeLang\Mapper\Mapping\Draft\MemberDraft;
use TypeLang\Mapper\Mapping\Reader\AttributeMetadataReader;
use TypeLang\Mapper\Mapping\Reader\PhpDocMetadataReader;
use TypeLang\Mapper\Mapping\Reader\ReflectionMetadataReader;
use TypeLang\Mapper\Mapping\Tests\Stub\Php84\BrokenHookStub;
use TypeLang\Mapper\Mapping\Tests\Stub\Php84\DescribedSetHookStub;
use TypeLang\Mapper\Mapping\Tests\Stub\Php84\HookStub;
use TypeLang\Mapper\Mapping\Tests\Stub\Php84\VirtualHookDocStub;
use TypeLang\Mapper\Mapping\Tests\TestCase;

#[RequiresPhp('>= 8.4.0')]
final class HookPhpDocMetadataReaderTest extends TestCase
{
    public function testVarOfPropertyWithStorageSetsBothTypesWhateverSetHookTakes(): void
    {
        $property = self::readHookProperty('name');

        self::assertType('non-empty-string', $property->read?->type);
        self::assertType('non-empty-string', $property->write?->type);
    }

    public function testVarOfPropertyWithStorageAndSetHookOfPropertyTypeSetsBothTypes(): void
    {
        $property = self::readHookProperty('items');

        self::assertType('list<int>', $property->read?->type);
        self::assertType('list<int>', $property->write?->type);
    }

    public function testHookDocBlocksOfPropertyWithStorageAreNotRead(): void
    {
        $property = self::readHookProperty('title');

        self::assertType('string', $property->read?->type);
        self::assertType('string', $property->write?->type);
    }

    public function testVarOfVirtualPropertyWithWiderSetHookSetsOnlyReadType(): void
    {
        $property = self::readHookProperty('name', VirtualHookDocStub::class);

        self::assertType('non-empty-string', $property->read?->type);
        self::assertType('string|int', $property->write?->type);
    }

    public function testVarOfVirtualPropertyWithSetHookOfPropertyTypeSetsBothTypes(): void
    {
        $property = self::readHookProperty('items', VirtualHookDocStub::class);

        self::assertType('list<int>', $property->read?->type);
        self::assertType('list<int>', $property->write?->type);
    }

    public function testTypesOfVirtualPropertyAreReadFromHookDocBlocks(): void
    {
        $property = self::readHookProperty('title', VirtualHookDocStub::class);

        self::assertType('non-empty-string', $property->read?->type);
        self::assertType('positive-int|non-empty-string', $property->write?->type);
    }

    public function testUnparsableSetHookParamIsSkippedForVar(): void
    {
        $property = self::readHookProperty('title', BrokenHookStub::class);

        self::assertType('non-empty-string', $property->read?->type);
        self::assertType('non-empty-string', $property->write?->type);
    }

    public function testSetHookDocBlockWithoutParamKeepsVar(): void
    {
        $property = self::readHookProperty('name', DescribedSetHookStub::class);

        self::assertType('non-empty-string', $property->read?->type);
        self::assertType('non-empty-string', $property->write?->type);
    }

    /**
     * @param non-empty-string $name
     * @param class-string $class
     * @throws \Throwable
     */
    private static function readHookProperty(string $name, string $class = HookStub::class): MemberDraft
    {
        $metadata = self::provide(
            $class,
            new AttributeMetadataReader(),
            new PhpDocMetadataReader(),
            new ReflectionMetadataReader(),
        );

        return self::getProperty($metadata, $name);
    }
}
