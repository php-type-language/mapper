<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Metadata;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Type\StringType;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;

final class ClassMetadataTest extends MetadataTestCase
{
    public function testConstruct(): void
    {
        $type = $this->createMock(StringType::class);
        $node = new NamedTypeNode('string');
        $typeMeta = new TypeMetadata(type: $type, statement: $node);

        $prop = new PropertyMetadata(
            name: 'name',
            alias: 'name',
            read: $typeMeta,
            write: $typeMeta,
        );

        $meta = new ClassMetadata(
            name: self::class,
            properties: ['name' => $prop],
            isNormalizeAsArray: true,
            typeErrorMessage: 'err',
            createdAt: 7,
        );

        self::assertSame(self::class, $meta->name);
        self::assertSame(['name' => $prop], $meta->properties);
        self::assertNull($meta->discriminator);
        self::assertTrue($meta->isNormalizeAsArray);
        self::assertSame('err', $meta->typeErrorMessage);
        self::assertSame(7, $meta->timestamp);
    }
}
