<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Reader;

use PHPUnit\Framework\Attributes\RequiresPhp;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Reader\AttributeMetadataReader;
use TypeLang\Mapper\Mapping\Tests\Stub\Php84\BrokenHookAttributeStub;
use TypeLang\Mapper\Mapping\Tests\Stub\Php84\HookAttributeStub;
use TypeLang\Mapper\Mapping\Tests\Stub\UserStub;
use TypeLang\Mapper\Mapping\Tests\TestCase;

#[RequiresPhp('>= 8.4.0')]
final class HookAttributeMetadataReaderTest extends TestCase
{
    public function testSetHookAttributeOverridesWriteTypeOfPropertyAttribute(): void
    {
        $property = self::readHookProperty('name');

        self::assertType('non-empty-string', $property->read?->type);
        self::assertType('int|string', $property->write?->type);
        self::assertTrue($property->strict);
    }

    public function testGetHookAttributeSetsOnlyReadType(): void
    {
        $property = self::readHookProperty('title');

        self::assertType(UserStub::class, $property->read?->type);
        self::assertType('string', $property->write?->type);
    }

    public function testSetHookAttributeSetsOnlyWriteType(): void
    {
        $property = self::readHookProperty('code');

        self::assertType('string', $property->read?->type);
        self::assertType('numeric-string', $property->write?->type);
    }

    public function testAttributeOfBypassedHookAndStrictnessOfGetHookAreErrors(): void
    {
        $class = BrokenHookAttributeStub::class;
        $attribute = MapType::class;
        $bypassed = 'hook has no effect, the mapper does not access the property through the hook';

        self::assertSame(
            [
                "{$class}::\$title: attribute \"{$attribute}\" of the get {$bypassed} in BrokenHookAttributeStub.php",
                "{$class}::\$tags: attribute \"{$attribute}\" of the set {$bypassed} in BrokenHookAttributeStub.php",
                "{$class}::\$code: argument \"strict\" of attribute \"{$attribute}\" of the get hook has no effect,"
                    . ' it applies to denormalization in BrokenHookAttributeStub.php',
            ],
            self::getErrors(static fn() => self::readOverReflection(new AttributeMetadataReader(), $class)),
        );
    }

    public function testAttributeOfBypassedHookKeepsTypeOfProperty(): void
    {
        try {
            self::readOverReflection(new AttributeMetadataReader(), BrokenHookAttributeStub::class);
        } catch (InvalidMetadataException $e) {
            self::assertType('string', self::getProperty($e->metadata, 'title')->read?->type);
            self::assertType('string', self::getProperty($e->metadata, 'tags')->write?->type);
            self::assertType('string', self::getProperty($e->metadata, 'code')->read?->type);

            return;
        }

        self::fail('Metadata is expected to be invalid');
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
