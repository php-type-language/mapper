<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader;

use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Mapping\Reader\ArrayReader;
use TypeLang\Mapper\Mapping\Reader\NullReader;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\SampleClass;

#[Group('reader')]
final class ReaderDelegationTest extends ReaderTestCase
{
    public function testArrayReaderDelegatesWhenNoConfig(): void
    {
        $reader = new ArrayReader(config: [], delegate: new NullReader());

        $info = $reader->read(new \ReflectionClass(SampleClass::class));

        self::assertSame(SampleClass::class, $info->name);
        self::assertSame([], $info->properties);
    }
}
