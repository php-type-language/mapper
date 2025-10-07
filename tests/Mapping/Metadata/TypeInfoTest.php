<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Metadata;

use TypeLang\Mapper\Mapping\Metadata\ParsedTypeInfo;
use TypeLang\Mapper\Mapping\Metadata\RawTypeInfo;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Mapper\Mapping\Metadata\TypeInfo;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;

final class TypeInfoTest extends MetadataTestCase
{
    public function testParsedTypeInfoConstructAndMixed(): void
    {
        $node = new NamedTypeNode('int');
        $info = new ParsedTypeInfo(statement: $node, source: new SourceInfo(__FILE__, __LINE__));

        self::assertSame($node, $info->statement);
        self::assertInstanceOf(SourceInfo::class, $info->source);

        self::assertSame(ParsedTypeInfo::mixed(), ParsedTypeInfo::mixed());
    }

    public function testRawTypeInfoConstructAndMixed(): void
    {
        $info = new RawTypeInfo(definition: 'string', source: new SourceInfo(__FILE__, __LINE__));

        self::assertSame('string', $info->definition);
        self::assertInstanceOf(SourceInfo::class, $info->source);

        self::assertSame(RawTypeInfo::mixed(), RawTypeInfo::mixed());
    }
}


