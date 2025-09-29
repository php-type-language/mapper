<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Metadata;

use PHPUnit\Framework\Attributes\CoversClass;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Type\IntType;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;

#[CoversClass(TypeMetadata::class)]
final class TypeMetadataTest extends MetadataTestCase
{
    public function testAccessors(): void
    {
        $type = new IntType();
        $stmt = new NamedTypeNode('int');
        $m = new TypeMetadata($type, $stmt, 1);

        self::assertSame($type, $m->getType());
        self::assertSame($stmt, $m->getTypeStatement());
        self::assertSame(1, $m->getTimestamp());
    }
}


