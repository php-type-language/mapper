<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Metadata;

use PHPUnit\Framework\Attributes\CoversClass;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;

#[CoversClass(TypeMetadata::class)]
final class TypeMetadataTest extends MetadataTestCase
{
    public function testAccessors(): void
    {
        $type = $this->createMock(TypeInterface::class);
        $stmt = new NamedTypeNode('int');
        $m = new TypeMetadata($type, $stmt, 1);

        self::assertSame($type, $m->type);
        self::assertSame($stmt, $m->statement);
        self::assertSame(1, $m->getTimestamp());
    }
}
