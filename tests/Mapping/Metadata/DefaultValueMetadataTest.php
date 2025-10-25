<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Metadata;

use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyMetadata\DefaultValueMetadata;

#[Group('meta')]
final class DefaultValueMetadataTest extends MetadataTestCase
{
    public function testConstruct(): void
    {
        $meta = new DefaultValueMetadata(value: 'abc', createdAt: 5);

        self::assertSame('abc', $meta->value);
        self::assertSame(5, $meta->timestamp);
    }
}
