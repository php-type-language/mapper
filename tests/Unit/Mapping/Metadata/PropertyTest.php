<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Unit\Mapping\Metadata;

use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Type\NullType;

final class PropertyTest extends MetadataTestCase
{
    public function testName(): void
    {
        $property = new PropertyMetadata('foo');

        self::assertSame('foo', $property->getName());
        self::assertSame('foo', $property->getExportName());

        $property->setExportName('foo2');

        self::assertSame('foo', $property->getName());
        self::assertSame('foo2', $property->getExportName());
    }

    public function testDefaultTimestamp(): void
    {
        $now = (new \DateTimeImmutable())->getTimestamp();

        $property = new PropertyMetadata('foo');

        self::assertGreaterThanOrEqual($now, $property->getTimestamp());
    }

    public function testTimestamp(): void
    {
        $property = new PropertyMetadata('foo', createdAt: 0xDEAD_BEEF);

        self::assertGreaterThanOrEqual(0xDEAD_BEEF, $property->getTimestamp());
    }

    public function testDefaultValue(): void
    {
        $property = new PropertyMetadata('foo');

        self::assertNull($property->getDefaultValue());
        self::assertFalse($property->hasDefaultValue());

        $property->setDefaultValue(0xDEAD_BEEF);

        self::assertSame(0xDEAD_BEEF, $property->getDefaultValue());
        self::assertTrue($property->hasDefaultValue());

        $property->removeDefaultValue();

        self::assertNull($property->getDefaultValue());
        self::assertFalse($property->hasDefaultValue());
    }

    public function testReadonlyModifier(): void
    {
        $property = new PropertyMetadata('foo');

        self::assertFalse($property->isReadonly());

        $property->markAsReadonly();

        self::assertTrue($property->isReadonly());

        $property->markAsReadonly(false);

        self::assertFalse($property->isReadonly());

        $property->markAsReadonly(true);

        self::assertTrue($property->isReadonly());
    }

    public function testType(): void
    {
        $property = new PropertyMetadata('foo');

        self::assertNull($property->getType());
        self::assertFalse($property->hasType());

        $property = new PropertyMetadata('foo', $type = new NullType());

        self::assertSame($type, $property->getType());
        self::assertTrue($property->hasType());

        $property->setType(new NullType());

        self::assertNotSame($type, $property->getType());
        self::assertTrue($property->hasType());

        $property->removeType();

        self::assertNull($property->getType());
        self::assertFalse($property->hasType());
    }
}
