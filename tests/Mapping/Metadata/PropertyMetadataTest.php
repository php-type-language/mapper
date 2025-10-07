<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Metadata;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyMetadata\DefaultValueMetadata;
use TypeLang\Mapper\Mapping\Metadata\Condition\NullConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Type\StringType;
use TypeLang\Parser\Node\Identifier;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;

final class PropertyMetadataTest extends MetadataTestCase
{
    public function testConstructAndFields(): void
    {
        $type = $this->createMock(StringType::class);
        $node = new NamedTypeNode('string');
        $typeMeta = new TypeMetadata(type: $type, statement: $node);

        $default = new DefaultValueMetadata(value: 'x');
        $skip = [new NullConditionMetadata()];

        $meta = new PropertyMetadata(
            name: 'title',
            alias: 'title',
            read: $typeMeta,
            write: $typeMeta,
            default: $default,
            skip: $skip,
            typeErrorMessage: 'type-err',
            undefinedErrorMessage: 'undef-err',
            createdAt: 9,
        );

        self::assertSame('title', $meta->name);
        self::assertSame('title', $meta->alias);
        self::assertSame($typeMeta, $meta->read);
        self::assertSame($typeMeta, $meta->write);
        self::assertSame($default, $meta->default);
        self::assertSame($skip, $meta->skip);
        self::assertSame('type-err', $meta->typeErrorMessage);
        self::assertSame('undef-err', $meta->undefinedErrorMessage);
        self::assertSame(9, $meta->timestamp);
    }

    public function testGetFieldNodeOptionalWhenDefault(): void
    {
        $type = $this->createMock(StringType::class);
        $node = new NamedTypeNode('string');
        $typeMeta = new TypeMetadata(type: $type, statement: $node);
        $default = new DefaultValueMetadata(value: 'x');

        $meta = new PropertyMetadata(
            name: 'title',
            alias: 'alias',
            read: $typeMeta,
            write: $typeMeta,
            default: $default,
        );

        $context = $this->createDenormalizationContext(0xDEAD_BEEF);
        $field = $meta->getFieldNode($context, true);

        self::assertTrue($field->optional);
        self::assertEquals(new Identifier('alias'), $field->key);
    }
}


