<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Runtime\Repository\Reference\Reader;

use TypeLang\Mapper\Runtime\Repository\Reference\Reader\NullReferencesReader;
use TypeLang\Mapper\Tests\Runtime\Repository\Reference\ReferenceTestCase;

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
