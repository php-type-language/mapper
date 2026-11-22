<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Metadata;

use TypeLang\Mapper\Mapping\Metadata\AccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\AccessPoint\HookAccessPoint;
use TypeLang\Mapper\Mapping\Metadata\AccessPoint\StorageAccessPoint;
use TypeLang\Mapper\Mapping\Metadata\DefaultValueMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\Tests\TestCase;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;

final class PropertyMetadataTest extends TestCase
{
    public function testExternalNameIsPhpNameUnlessGiven(): void
    {
        $property = new PropertyMetadata('userId');

        self::assertSame('userId', $property->externalName);
        self::assertFalse($property->isExternalNameExplicit);
    }

    public function testGivenExternalNameIsExplicit(): void
    {
        $property = (new PropertyMetadata('userId'))->withExternalName('user_id');

        self::assertSame('user_id', $property->externalName);
        self::assertTrue($property->isExternalNameExplicit);
        self::assertTrue((new PropertyMetadata('userId', externalName: 'user_id'))->isExternalNameExplicit);
    }

    public function testBothDirectionsAccessStorageWithoutTypeByDefault(): void
    {
        $property = new PropertyMetadata('userId');

        self::assertEquals(new AccessMetadata(new StorageAccessPoint('userId')), $property->read);
        self::assertEquals(new AccessMetadata(new StorageAccessPoint('userId')), $property->write);
        self::assertNull($property->strict);
    }

    public function testTypeIsTreeOrError(): void
    {
        $type = self::createType('int');
        $error = TypeMetadata::createFromError('Unexpected end of input', new SourceInfo());

        self::assertNotNull($type->type);
        self::assertNull($type->error);
        self::assertNull($error->type);
        self::assertSame('Unexpected end of input', $error->error);
    }

    public function testWithersKeepOtherValues(): void
    {
        $read = self::createType('int');
        $write = self::createType('string');
        $default = new DefaultValueMetadata(null, new SourceInfo());

        $property = (new PropertyMetadata('id'))
            ->withExternalName('user_id')
            ->withReadType($read)
            ->withWriteType($write)
            ->withStrict(true)
            ->withDefaultValue($default);

        self::assertSame('id', $property->name);
        self::assertSame('user_id', $property->externalName);
        self::assertSame($read, $property->read?->type);
        self::assertSame($write, $property->write?->type);
        self::assertTrue($property->strict);
        self::assertSame($default, $property->default);
    }

    public function testTypeKeepsPointOfDirection(): void
    {
        $type = self::createType('int');
        $hook = new AccessMetadata(new HookAccessPoint('id'));

        $property = (new PropertyMetadata('id'))->withWriteAccess($hook)->withType($type);

        self::assertInstanceOf(StorageAccessPoint::class, $property->read?->point);
        self::assertSame($hook->point, $property->write?->point);
        self::assertSame($type, $property->read->type);
        self::assertSame($type, $property->write->type);
    }

    public function testTypeDoesNotAddMissingDirection(): void
    {
        $type = self::createType('int');

        $property = (new PropertyMetadata('id'))
            ->withReadAccess(null)
            ->withType($type)
            ->withReadType($type);

        self::assertNull($property->read);
        self::assertSame($type, $property->write?->type);
        self::assertNull($property->withWriteAccess(null)->withWriteType($type)->write);
    }

    public function testWitherDoesNotChangeOriginal(): void
    {
        $property = new PropertyMetadata('id');

        $property->withExternalName('user_id');
        $property->withDefaultValue(new DefaultValueMetadata(1, new SourceInfo()));

        self::assertSame('id', $property->externalName);
        self::assertFalse($property->isExternalNameExplicit);
        self::assertNull($property->default);
    }

    /**
     * @param non-empty-string $name
     */
    private static function createType(string $name): TypeMetadata
    {
        return TypeMetadata::createFromType(new NamedTypeNode(new Name($name)), new SourceInfo());
    }
}
