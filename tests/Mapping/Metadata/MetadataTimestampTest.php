<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Metadata;

use TypeLang\Mapper\Mapping\Metadata\Metadata;

final class MetadataTimestampTest extends MetadataTestCase
{
    public function testTimestampIsSetByDefault(): void
    {
        $meta = new class() extends Metadata {};

        self::assertIsInt($meta->timestamp);
        self::assertGreaterThan(0, $meta->timestamp);
    }

    public function testCustomTimestamp(): void
    {
        $ts = 1234567890;
        $meta = new class($ts) extends Metadata {};

        self::assertSame($ts, $meta->timestamp);
    }
}


