<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Metadata;

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

    public function testStrictnessIsOptionOfType(): void
    {
        $type = self::createType('int');

        self::assertNull($type->strict);
        self::assertTrue($type->withStrict(true)->strict);
        self::assertSame($type->type, $type->withStrict(true)->type);
    }

    public function testTypeIsTreeOrError(): void
    {
        $type = self::createType('int');
        $error = TypeMetadata::createFromError('Unexpected end of input', new SourceInfo());

        self::assertNotNull($type->type);
        self::assertNull($type->error);
        self::assertNull($error->type);
        self::assertSame('Unexpected end of input', $error->error);
        self::assertSame('Unexpected end of input', $error->withStrict(true)->error);
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
            ->withDefaultValue($default);

        self::assertSame('id', $property->name);
        self::assertSame('user_id', $property->externalName);
        self::assertSame($read, $property->read);
        self::assertSame($write, $property->write);
        self::assertSame($default, $property->default);
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
