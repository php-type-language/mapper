<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Metadata;

use PHPUnit\Framework\Attributes\CoversClass;
use TypeLang\Mapper\Mapping\Metadata\EmptyConditionMetadata;

#[CoversClass(EmptyConditionMetadata::class)]
final class EmptyConditionMetadataTest extends MetadataTestCase
{
    public function testMatch(): void
    {
        $cond = new EmptyConditionMetadata();

        self::assertTrue($cond->match(new \stdClass(), null));
        self::assertTrue($cond->match(new \stdClass(), 0));
        self::assertTrue($cond->match(new \stdClass(), ''));
        self::assertTrue($cond->match(new \stdClass(), []));

        self::assertFalse($cond->match(new \stdClass(), 1));
        self::assertFalse($cond->match(new \stdClass(), 'x'));
        self::assertFalse($cond->match(new \stdClass(), [1]));
    }
}
