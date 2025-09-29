<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Metadata;

use PHPUnit\Framework\Attributes\CoversClass;
use TypeLang\Mapper\Mapping\Metadata\MatchConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;

#[CoversClass(PropertyMetadata::class)]
final class PropertyMetadataTest extends MetadataTestCase
{
    public function testNames(): void
    {
        $m = new PropertyMetadata('export');
        self::assertSame('export', $m->alias);
        self::assertSame('export', $m->name);
        $m->alias = 'pub';
        self::assertSame('pub', $m->alias);
        self::assertSame('export', $m->name);
    }

    public function testTypeInfo(): void
    {
        $type = $this->createMock(TypeInterface::class);
        $stmt = new NamedTypeNode('int');
        $tm = new TypeMetadata($type, $stmt);

        $m = new PropertyMetadata('a');
        self::assertNull($m->type);

        $m->type = $tm;
        self::assertSame($tm, $m->type);

        $m->type = null;
        self::assertNull($m->type);
    }

    public function testSkipConditions(): void
    {
        $m = new PropertyMetadata('x');
        $condA = new class extends MatchConditionMetadata {
            public function match(object $object, mixed $value): bool
            {
                return true;
            }
        };
        $condB = new class extends MatchConditionMetadata {
            public function match(object $object, mixed $value): bool
            {
                return false;
            }
        };
        $m->addSkipCondition($condA);
        $m->addSkipCondition($condB);

        $conds = $m->getSkipConditions();
        self::assertCount(2, $conds);
        self::assertSame([$condA, $condB], $conds);
    }

    public function testDefaultValueManagement(): void
    {
        $m = new PropertyMetadata('y');
        self::assertFalse($m->hasDefaultValue());
        self::assertNull($m->findDefaultValue());

        $m->setDefaultValue(10);
        self::assertTrue($m->hasDefaultValue());
        self::assertSame(10, $m->findDefaultValue());

        $m->removeDefaultValue();
        self::assertFalse($m->hasDefaultValue());
        self::assertNull($m->findDefaultValue());
    }
}
