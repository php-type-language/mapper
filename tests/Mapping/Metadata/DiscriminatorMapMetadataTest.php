<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Metadata;

use PHPUnit\Framework\Attributes\CoversClass;
use TypeLang\Mapper\Mapping\Metadata\DiscriminatorMapMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Type\IntType;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;

#[CoversClass(DiscriminatorMapMetadata::class)]
final class DiscriminatorMapMetadataTest extends MetadataTestCase
{
    public function testBasics(): void
    {
        $m = new DiscriminatorMapMetadata('kind');
        self::assertSame('kind', $m->getField());
        self::assertSame([], $m->getMapping());
        self::assertNull($m->getDefaultType());
        self::assertFalse($m->hasType('x'));
        self::assertNull($m->findType('x'));
    }

    public function testAddAndFindType(): void
    {
        $type = new IntType();
        $stmt = new NamedTypeNode('int');
        $tm = new TypeMetadata($type, $stmt);

        $m = new DiscriminatorMapMetadata('kind');
        $m->addType('int', $tm);

        self::assertTrue($m->hasType('int'));
        self::assertSame($tm, $m->findType('int'));
        self::assertArrayHasKey('int', $m->getMapping());
    }

    public function testDefaultType(): void
    {
        $type = new IntType();
        $stmt = new NamedTypeNode('int');
        $tm = new TypeMetadata($type, $stmt);

        $m = new DiscriminatorMapMetadata('kind');
        $m->setDefaultType($tm);
        self::assertSame($tm, $m->getDefaultType());

        $m->setDefaultType(null);
        self::assertNull($m->getDefaultType());
    }
}


