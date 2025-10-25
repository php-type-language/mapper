<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader;

use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Mapping\Reader\YamlConfigReader;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\SampleClass;

#[Group('reader')]
final class YamlConfigReaderTest extends ReaderTestCase
{
    public function testYamlConfigReaderLoadsYamlFile(): void
    {
        $reader = new YamlConfigReader($this->getConfigDirectory('YamlConfigReaderTest'));
        $info = $reader->read(new \ReflectionClass(SampleClass::class));

        self::assertArrayHasKey('name', $info->properties);
    }

    public function testReadsAllSectionsFromYaml(): void
    {
        $reader = new YamlConfigReader($this->getConfigDirectory('YamlConfigReaderTestAll'));
        $info = $reader->read(new \ReflectionClass(SampleClass::class));

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
