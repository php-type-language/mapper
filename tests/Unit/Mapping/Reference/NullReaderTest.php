<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Unit\Mapping\Reference;

use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Mapping\Reference\NullReferencesReader;

#[Group('unit'), Group('type-lang/mapper')]
final class NullReaderTest extends ReferenceTestCase
{
    public function testReturnsNothing(): void
    {
        $reader = new NullReferencesReader();

        self::assertSame([], $reader->getUseStatements(
            new \ReflectionClass(self::class),
        ));
    }
}
