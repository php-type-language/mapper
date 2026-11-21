<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Reader;

use PHPUnit\Framework\Attributes\RequiresPhp;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Reader\PhpDocMetadataReader;
use TypeLang\Mapper\Mapping\Tests\Stub\Php84\BrokenHookStub;
use TypeLang\Mapper\Mapping\Tests\Stub\Php84\HookStub;
use TypeLang\Mapper\Mapping\Tests\TestCase;

#[RequiresPhp('>= 8.4.0')]
final class HookPhpDocMetadataReaderTest extends TestCase
{
    public function testVarOfPropertyWithWiderSetHookSetsOnlyReadType(): void
    {
        $property = self::readHookProperty('name');

        self::assertType('non-empty-string', $property->read);
        self::assertType('string|int', $property->write);
    }

    public function testVarOfPropertyWithSetHookOfPropertyTypeSetsBothTypes(): void
    {
        $property = self::readHookProperty('items');

        self::assertType('list<int>', $property->read);
        self::assertType('list<int>', $property->write);
    }

    public function testTypesAreReadFromHookDocBlocks(): void
    {
        $property = self::readHookProperty('title');

        self::assertType('non-empty-string', $property->read);
        self::assertType('positive-int|non-empty-string', $property->write);
    }

    public function testUnparsableSetHookParamIsNotRead(): void
    {
        $property = self::readHookProperty('title', BrokenHookStub::class);

        self::assertType('non-empty-string', $property->read);
        self::assertType('string', $property->write);
    }

    /**
     * @param non-empty-string $name
     * @param class-string $class
     * @throws \Throwable
     */
    private static function readHookProperty(string $name, string $class = HookStub::class): PropertyMetadata
    {
        $metadata = self::readOverReflection(new PhpDocMetadataReader(), $class);

        return self::getProperty($metadata, $name);
    }
}
