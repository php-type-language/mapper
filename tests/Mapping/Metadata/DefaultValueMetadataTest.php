<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Metadata;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyMetadata\DefaultValueMetadata;

final class DefaultValueMetadataTest extends MetadataTestCase
{
    public function testConstruct(): void
    {
        $meta = new DefaultValueMetadata(value: 'abc', createdAt: 5);

        self::assertSame('abc', $meta->value);
        self::assertSame(5, $meta->timestamp);
    }
}


