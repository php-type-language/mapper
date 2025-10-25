<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Metadata;

use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\DiscriminatorMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Type\StringType;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;

#[Group('meta')]
final class DiscriminatorMetadataTest extends MetadataTestCase
{
    public function testConstruct(): void
    {
        $type = $this->createMock(StringType::class);
        $node = new NamedTypeNode('string');
        $typeMeta = new TypeMetadata(type: $type, statement: $node);

        $map = ['a' => $typeMeta];
        $meta = new DiscriminatorMetadata(field: 'kind', map: $map, default: null, createdAt: 11);

        self::assertSame('kind', $meta->field);
        self::assertSame($map, $meta->map);
        self::assertNull($meta->default);
        self::assertSame(11, $meta->timestamp);
    }
}
