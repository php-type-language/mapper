<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Metadata;

use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Mapping\Metadata\ClassInfo;

#[Group('meta')]
final class ClassInfoTest extends MetadataTestCase
{
    public function testDefaultsAndGetPropertyOrCreate(): void
    {
        $info = new ClassInfo(name: self::class);

        self::assertSame(self::class, $info->name);
        self::assertNull($info->discriminator);
        self::assertNull($info->typeErrorMessage);
        self::assertNull($info->isNormalizeAsArray);
        self::assertSame([], $info->properties);

        $prop = $info->getPropertyOrCreate('id');
        self::assertArrayHasKey('id', $info->properties);
        self::assertSame($prop, $info->getPropertyOrCreate('id'));
    }
}
