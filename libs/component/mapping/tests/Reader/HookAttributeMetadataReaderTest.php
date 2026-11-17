<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Reader;

use PHPUnit\Framework\Attributes\RequiresPhp;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Reader\AttributeMetadataReader;
use TypeLang\Mapper\Mapping\Tests\Stub\Php84\HookAttributeStub;
use TypeLang\Mapper\Mapping\Tests\Stub\UserStub;
use TypeLang\Mapper\Mapping\Tests\TestCase;

#[RequiresPhp('>= 8.4.0')]
final class HookAttributeMetadataReaderTest extends TestCase
{
    public function testSetHookAttributeOverridesWriteTypeOfPropertyAttribute(): void
    {
        $property = self::readHookProperty('name');

        self::assertType('non-empty-string', $property->read);
        self::assertNull($property->read->isStrict);
        self::assertType('int|string', $property->write);
        self::assertTrue($property->write->isStrict);
    }

    public function testGetHookAttributeSetsOnlyReadType(): void
    {
        $property = self::readHookProperty('title');

        self::assertType(UserStub::class, $property->read);
        self::assertType('string', $property->write);
    }

    public function testSetHookAttributeSetsOnlyWriteType(): void
    {
        $property = self::readHookProperty('code');

        self::assertType('string', $property->read);
        self::assertType('numeric-string', $property->write);
    }

    /**
     * @param non-empty-string $name
     */
    private static function readHookProperty(string $name): PropertyMetadata
    {
        $metadata = self::readOverReflection(new AttributeMetadataReader(), HookAttributeStub::class);

        return self::getProperty($metadata, $name);
    }
}
