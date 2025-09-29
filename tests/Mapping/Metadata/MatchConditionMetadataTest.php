<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Metadata;

use PHPUnit\Framework\Attributes\CoversClass;
use TypeLang\Mapper\Mapping\Metadata\MatchConditionMetadata;

#[CoversClass(MatchConditionMetadata::class)]
final class MatchConditionMetadataTest extends MetadataTestCase
{
    public function testAbstractContract(): void
    {
        $impl = new class extends MatchConditionMetadata {
            public function match(object $object, mixed $value): bool
            {
                return $value === 1;
            }
        };

        self::assertTrue($impl->match(new \stdClass(), 1));
        self::assertFalse($impl->match(new \stdClass(), 2));
    }

    public function testTimestampInheritance(): void
    {
        $impl = new class (123) extends MatchConditionMetadata {
            public function match(object $object, mixed $value): bool
            {
                return true;
            }
        };

        self::assertSame(123, $impl->timestamp);
    }

    public function testMatchWithDifferentObjects(): void
    {
        $impl = new class extends MatchConditionMetadata {
            public function match(object $object, mixed $value): bool
            {
                return $object instanceof \stdClass && $value === 'test';
            }
        };

        self::assertTrue($impl->match(new \stdClass(), 'test'));
        self::assertFalse($impl->match(new \stdClass(), 'other'));
        self::assertFalse($impl->match(new \DateTime(), 'test'));
    }
}
