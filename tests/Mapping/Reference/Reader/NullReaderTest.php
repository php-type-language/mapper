<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reference\Reader;

use TypeLang\Mapper\Mapping\Reference\Reader\NullReferencesReader;
use TypeLang\Mapper\Tests\Mapping\Reference\ReferenceTestCase;

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
