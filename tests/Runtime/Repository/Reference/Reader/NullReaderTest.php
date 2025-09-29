<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Runtime\Repository\Reference\Reader;

use PHPUnit\Framework\Attributes\CoversClass;
use TypeLang\Mapper\Runtime\Repository\Reference\Reader\NullReferencesReader;
use TypeLang\Mapper\Tests\Runtime\Repository\Reference\ReferenceTestCase;

#[CoversClass(NullReferencesReader::class)]
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
