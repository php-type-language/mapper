<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader;

use TypeLang\Mapper\Mapping\Reader\ArrayReader;
use TypeLang\Mapper\Mapping\Reader\NullReader;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\DummyParser;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\SampleClass;

final class ReaderDelegationTest extends ReaderTestCase
{
    public function testArrayReaderDelegatesWhenNoConfig(): void
    {
        $reader = new ArrayReader(config: [], delegate: new NullReader());

        $info = $reader->read(new \ReflectionClass(SampleClass::class), new DummyParser());

        self::assertSame(SampleClass::class, $info->name);
        self::assertSame([], $info->properties);
    }
}


