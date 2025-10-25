<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader;

use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Mapping\Reader\JsonConfigReader;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\SampleClass;

#[Group('reader')]
final class ConfigReaderErrorsTest extends ReaderTestCase
{
    public function testJsonReaderThrowsWhenCannotReadFile(): void
    {
        $reader = new JsonConfigReader($this->getConfigDirectory('ConfigReaderErrorsTest'));

        $this->expectException(\JsonException::class);
        $reader->read(new \ReflectionClass(SampleClass::class));
    }
}
