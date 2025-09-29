<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Metadata;

use PHPUnit\Framework\Attributes\CoversClass;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\DiscriminatorMapMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;

#[CoversClass(ClassMetadata::class)]
final class ClassMetadataTest extends MetadataTestCase
{
    public function testConstructorInitializesPropertiesDiscriminatorAndTimestamp(): void
    {
        $propA = new PropertyMetadata('a');
        $propB = new PropertyMetadata('b');
        $disc = new DiscriminatorMapMetadata('kind');

        $m = new ClassMetadata(\stdClass::class, [$propA, $propB], $disc, 777);

        self::assertSame(\stdClass::class, $m->getName());
        self::assertTrue($m->hasProperty('a'));
        self::assertTrue($m->hasProperty('b'));
        self::assertSame($propA, $m->findProperty('a'));
        self::assertSame($propB, $m->findProperty('b'));
        self::assertTrue($m->hasDiscriminator());
        self::assertSame($disc, $m->findDiscriminator());
        self::assertSame(777, $m->getTimestamp());
    }

    public function testNameGetter(): void
    {
        $m = new ClassMetadata(\stdClass::class);
        self::assertSame(\stdClass::class, $m->getName());
    }

    public function testNormalizeAsArrayFlag(): void
    {
        $m = new ClassMetadata(\stdClass::class);
        self::assertNull($m->isNormalizeAsArray());
        $m->shouldNormalizeAsArray(true);
        self::assertTrue($m->isNormalizeAsArray());
        $m->shouldNormalizeAsArray(false);
        self::assertFalse($m->isNormalizeAsArray());
        $m->shouldNormalizeAsArray(null);
        self::assertNull($m->isNormalizeAsArray());
    }

    public function testAddAndGetProperties(): void
    {
        $m = new ClassMetadata(\stdClass::class);
        $propA = new PropertyMetadata('a');
        $propB = new PropertyMetadata('b');
        $m->addProperty($propA);
        $m->addProperty($propB);

        self::assertTrue($m->hasProperty('a'));
        self::assertTrue($m->hasProperty('b'));
        self::assertSame($propA, $m->findProperty('a'));
        self::assertSame($propB, $m->findProperty('b'));

        $props = $m->getProperties();
        self::assertCount(2, $props);
        self::assertContains($propA, $props);
        self::assertContains($propB, $props);
    }

    public function testGetPropertyOrCreate(): void
    {
        $m = new ClassMetadata(\stdClass::class);
        $created = $m->getPropertyOrCreate('first');
        self::assertInstanceOf(PropertyMetadata::class, $created);
        self::assertSame($created, $m->findProperty('first'));
        $same = $m->getPropertyOrCreate('first');
        self::assertSame($created, $same);
    }

    public function testDiscriminatorManagement(): void
    {
        $m = new ClassMetadata(\stdClass::class);
        self::assertFalse($m->hasDiscriminator());
        self::assertNull($m->findDiscriminator());

        $disc = new DiscriminatorMapMetadata('kind');
        $m->setDiscriminator($disc);

        self::assertTrue($m->hasDiscriminator());
        self::assertSame($disc, $m->findDiscriminator());

        $m->setDiscriminator(null);
        self::assertFalse($m->hasDiscriminator());
        self::assertNull($m->findDiscriminator());
    }
}
