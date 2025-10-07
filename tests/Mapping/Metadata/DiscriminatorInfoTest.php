<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Metadata;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\DiscriminatorInfo;
use TypeLang\Mapper\Mapping\Metadata\ParsedTypeInfo;

final class DiscriminatorInfoTest extends MetadataTestCase
{
    public function testConstruct(): void
    {
        $map = [
            'a' => ParsedTypeInfo::mixed(),
        ];

        $info = new DiscriminatorInfo(field: 'type', map: $map, default: null);

        self::assertSame('type', $info->field);
        self::assertSame($map, $info->map);
        self::assertNull($info->default);
    }
}
