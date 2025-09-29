<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Runtime\Repository\Reference;

use TypeLang\Mapper\Runtime\Repository\Reference\NullReferencesReader;

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
