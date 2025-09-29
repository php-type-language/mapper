<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Metadata;

use PHPUnit\Framework\Attributes\CoversClass;
use TypeLang\Mapper\Mapping\Metadata\NullConditionMetadata;

#[CoversClass(NullConditionMetadata::class)]
final class NullConditionMetadataTest extends MetadataTestCase
{
    public function testMatch(): void
    {
        $cond = new NullConditionMetadata();

        self::assertTrue($cond->match(new \stdClass(), null));
        self::assertFalse($cond->match(new \stdClass(), 0));
        self::assertFalse($cond->match(new \stdClass(), ''));
        self::assertFalse($cond->match(new \stdClass(), false));
    }
}
