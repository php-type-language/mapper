<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Metadata;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\DefaultValueMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\Tests\TestCase;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;

final class ClassMetadataTest extends TestCase
{
    public function testPropertiesKeepOrderOfAddition(): void
    {
        $metadata = (new ClassMetadata(\stdClass::class))
            ->withProperty(new PropertyMetadata('b'))
            ->withProperty(new PropertyMetadata('a'));

        self::assertSame(['b', 'a'], \array_keys($metadata->properties));
    }

    public function testPropertyOfSameNameIsReplacedInPlace(): void
    {
        $replacement = (new PropertyMetadata('a'))->withExternalName('first');

        $metadata = (new ClassMetadata(\stdClass::class))
            ->withProperty(new PropertyMetadata('a'))
            ->withProperty(new PropertyMetadata('b'))
            ->withProperty($replacement);

        self::assertSame(['a', 'b'], \array_keys($metadata->properties));
        self::assertSame($replacement, $metadata->findProperty('a'));
        self::assertNull($metadata->findProperty('c'));
    }

    public function testWitherDoesNotChangeOriginal(): void
    {
        $metadata = new ClassMetadata(\stdClass::class);

        $metadata->withProperty(new PropertyMetadata('a'));

        self::assertSame([], $metadata->properties);
    }

    public function testSerializationKeepsTypesAndSources(): void
    {
        $source = new SourceInfo('/app/mapping.yaml', '$.properties.id.type');
        $type = TypeMetadata::createFromType(new NamedTypeNode(new Name('int')), $source)->withStrict(true);
        $error = TypeMetadata::createFromError('Unexpected end of input', $source);

        $property = (new PropertyMetadata('id'))
            ->withWriteType($type)
            ->withReadType($error)
            ->withDefaultValue(new DefaultValueMetadata(1, $source));

        $metadata = (new ClassMetadata(\stdClass::class))->withProperty($property);

        self::assertEquals($metadata, \unserialize(\serialize($metadata)));
    }
}
