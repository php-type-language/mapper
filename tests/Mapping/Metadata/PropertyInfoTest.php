<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Metadata;

use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyInfo;
use TypeLang\Mapper\Mapping\Metadata\ParsedTypeInfo;

#[Group('meta')]
final class PropertyInfoTest extends MetadataTestCase
{
    public function testDefaults(): void
    {
        $info = new PropertyInfo(name: 'title');

        self::assertSame('title', $info->name);
        self::assertSame('title', $info->alias);
        self::assertSame(ParsedTypeInfo::mixed(), $info->read);
        self::assertSame(ParsedTypeInfo::mixed(), $info->write);
        self::assertNull($info->default);
        self::assertSame([], $info->skip);
        self::assertNull($info->typeErrorMessage);
        self::assertNull($info->undefinedErrorMessage);
    }
}
