<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader;

use TypeLang\Mapper\Mapping\Reader\ArrayReader;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\SampleClass;

final class ArrayReaderTest extends ReaderTestCase
{
    public function testLoadsNormalizeAsArrayAndType(): void
    {
        $config = [
            SampleClass::class => [
                'normalize_as_array' => true,
                'properties' => [
                    'name' => [
                        'type' => 'string',
                    ],
                ],
            ],
        ];

        $reader = new ArrayReader($config);
        $info = $reader->read(new \ReflectionClass(SampleClass::class), $this->createTypeParser());

        self::assertTrue($info->isNormalizeAsArray);
        self::assertSame('string', $info->properties['name']->read->definition);
        self::assertSame('string', $info->properties['name']->write->definition);
    }

    public function testLoadsAliasAndErrorMessages(): void
    {
        $config = [
            SampleClass::class => [
                'properties' => [
                    'name' => [
                        'type' => 'string',
                        'name' => 'label',
                        'type_error_message' => 'bad-type',
                        'undefined_error_message' => 'missing',
                    ],
                ],
            ],
        ];

        $reader = new ArrayReader($config);
        $info = $reader->read(new \ReflectionClass(SampleClass::class), $this->createTypeParser());

        $prop = $info->properties['name'];
        self::assertSame('label', $prop->alias);
        self::assertSame('bad-type', $prop->typeErrorMessage);
        self::assertSame('missing', $prop->undefinedErrorMessage);
    }

    public function testLoadsSkipConditionsStringAndList(): void
    {
        $config = [
            SampleClass::class => [
                'properties' => [
                    'name' => [
                        'type' => 'string',
                        'skip' => 'null',
                    ],
                    'age' => [
                        'type' => 'int',
                        'skip' => ['empty', 'this.flag == false'],
                    ],
                ],
            ],
        ];

        $reader = new ArrayReader($config);
        $info = $reader->read(new \ReflectionClass(SampleClass::class), $this->createTypeParser());

        self::assertCount(1, $info->properties['name']->skip);
        self::assertCount(2, $info->properties['age']->skip);
    }

    public function testLoadsDiscriminatorWithOtherwise(): void
    {
        $config = [
            SampleClass::class => [
                'discriminator' => [
                    'field' => 'kind',
                    'map' => [
                        'a' => 'A',
                        'b' => 'B',
                    ],
                    'otherwise' => 'C',
                ],
            ],
        ];

        $reader = new ArrayReader($config);
        $info = $reader->read(new \ReflectionClass(SampleClass::class), $this->createTypeParser());

        self::assertNotNull($info->discriminator);
        self::assertSame('kind', $info->discriminator?->field);
        self::assertArrayHasKey('a', $info->discriminator?->map);
        self::assertSame('C', $info->discriminator?->default?->definition ?? null);
    }

    public function testGracefullyHandlesMissingOptionalSections(): void
    {
        $config = [
            SampleClass::class => [
                // no normalize_as_array
                // no discriminator
                'properties' => [
                    'name' => 'string',
                ],
            ],
        ];

        $reader = new ArrayReader($config);
        $info = $reader->read(new \ReflectionClass(SampleClass::class), $this->createTypeParser());

        self::assertNull($info->isNormalizeAsArray);
        self::assertNull($info->discriminator);
        self::assertSame('string', $info->properties['name']->read->definition);
    }
}


