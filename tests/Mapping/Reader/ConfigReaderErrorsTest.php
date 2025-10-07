<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader;

use TypeLang\Mapper\Mapping\Reader\JsonConfigReader;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\DummyParser;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\SampleClass;

final class ConfigReaderErrorsTest extends ReaderTestCase
{
    public function testJsonReaderThrowsWhenCannotReadFile(): void
    {
        $reader = new JsonConfigReader($this->getConfigDirectory('ConfigReaderErrorsTest'));

        $this->expectException(\JsonException::class);
        $reader->read(new \ReflectionClass(SampleClass::class), new DummyParser());
    }
}


