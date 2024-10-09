<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Unit\Mapping\Metadata;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;

final class ClassTest extends MetadataTestCase
{
    public function testName(): void
    {
        $class = new ClassMetadata(self::class);

        self::assertSame(self::class, $class->getName());
        self::assertCount(0, $class->getProperties());
    }

    public function testClassWithProperties(): void
    {
        $class = new ClassMetadata(self::class, [
            new PropertyMetadata('prop'),
        ]);

        self::assertCount(1, $class->getProperties());
        self::assertNotNull($class->findProperty('prop'));
        self::assertTrue($class->hasProperty('prop'));
        self::assertNull($class->findProperty('prop2'));
        self::assertFalse($class->hasProperty('prop2'));

        $class->getPropertyOrCreate('prop2');

        self::assertCount(2, $class->getProperties());
        self::assertNotNull($class->findProperty('prop'));
        self::assertTrue($class->hasProperty('prop'));
        self::assertNotNull($class->findProperty('prop2'));
        self::assertTrue($class->hasProperty('prop2'));
    }
}
