<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Unit\Mapping\Metadata;

use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Platform\Standard\Type\NullType;
use TypeLang\Parser\Node\Literal\NullLiteralNode;

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

        self::assertNull($property->findDefaultValue());
        self::assertFalse($property->hasDefaultValue());

        $property->setDefaultValue(0xDEAD_BEEF);

        self::assertSame(0xDEAD_BEEF, $property->findDefaultValue());
        self::assertTrue($property->hasDefaultValue());

        $property->removeDefaultValue();

        self::assertNull($property->findDefaultValue());
        self::assertFalse($property->hasDefaultValue());
    }

    public function testType(): void
    {
        $property = new PropertyMetadata('foo');

        self::assertNull($property->findTypeInfo());
        self::assertFalse($property->hasTypeInfo());

        $property = new PropertyMetadata('foo', new TypeMetadata(
            type: $type = new NullType(),
            statement: $statement = new NullLiteralNode(),
        ));

        self::assertNotNull($info = $property->findTypeInfo());
        self::assertSame($type, $info->getType());
        self::assertSame($statement, $info->getTypeStatement());
        self::assertTrue($property->hasTypeInfo());

        $property->setTypeInfo(new TypeMetadata(
            type: new NullType(),
            statement: new NullLiteralNode()
        ));

        self::assertNotNull($info = $property->findTypeInfo());
        self::assertNotSame($type, $info->getType());
        self::assertNotSame($statement, $info->getTypeStatement());
        self::assertTrue($property->hasTypeInfo());

        $property->removeTypeInfo();

        self::assertNull($property->findTypeInfo());
        self::assertFalse($property->hasTypeInfo());
    }
}
