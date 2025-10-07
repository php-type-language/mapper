<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader;

use TypeLang\Mapper\Mapping\Reader\JsonConfigReader;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\SampleClass;

final class JsonConfigReaderTest extends ReaderTestCase
{
    public function testJsonConfigReaderLoadsJsonFile(): void
    {
        $reader = new JsonConfigReader($this->getConfigDirectory('JsonConfigReaderTest'));
        $info = $reader->read(new \ReflectionClass(SampleClass::class), $this->createTypeParser());

        self::assertArrayHasKey('name', $info->properties);
    }

    public function testReadsAllSectionsFromJson(): void
    {
        $reader = new JsonConfigReader($this->getConfigDirectory('JsonConfigReaderTestAll'));
        $info = $reader->read(new \ReflectionClass(SampleClass::class), $this->createTypeParser());

        self::assertTrue($info->isNormalizeAsArray);

        $prop = $info->properties['name'];
        self::assertSame('label', $prop->alias);
        self::assertSame('string', $prop->read->definition);
        self::assertSame('string', $prop->write->definition);
        self::assertSame('bad-type', $prop->typeErrorMessage);
        self::assertSame('missing', $prop->undefinedErrorMessage);
        self::assertCount(3, $prop->skip);

        self::assertNotNull($info->discriminator);
        self::assertSame('kind', $info->discriminator?->field);
        self::assertArrayHasKey('a', $info->discriminator?->map);
        self::assertSame('C', $info->discriminator?->default?->definition ?? null);
    }
}
