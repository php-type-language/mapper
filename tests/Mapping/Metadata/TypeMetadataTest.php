<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Metadata;

use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Type\StringType;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;

final class TypeMetadataTest extends MetadataTestCase
{
    public function testConstruct(): void
    {
        $type = $this->createMock(StringType::class);
        $node = new NamedTypeNode('string');
        $meta = new TypeMetadata(type: $type, statement: $node, createdAt: 42);

        self::assertSame($type, $meta->type);
        self::assertSame($node, $meta->statement);
        self::assertSame(42, $meta->timestamp);
    }
}


