<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader;

use PHPUnit\Framework\Attributes\RequiresPhp;
use TypeLang\Mapper\Mapping\Reader\ReflectionReader;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\DummyParser;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\SampleClass;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\TypesClass;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\TypesHooksClass;

final class ReflectionReaderTest extends ReaderTestCase
{
    public function testLoadsPublicProperties(): void
    {
        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass(SampleClass::class), new DummyParser());

        self::assertArrayHasKey('name', $info->properties);
        self::assertArrayHasKey('age', $info->properties);

        self::assertSame('name', $info->properties['name']->name);
        self::assertSame('age', $info->properties['age']->name);
    }

    public function testFiltersNonPublicAndStaticProperties(): void
    {
        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass(TypesClass::class), new DummyParser());

        self::assertArrayHasKey('intProp', $info->properties);
        self::assertArrayNotHasKey('protectedProp', $info->properties);
        self::assertArrayNotHasKey('privateProp', $info->properties);
        self::assertArrayNotHasKey('staticProp', $info->properties);
    }

    public function testClassSourceIsFilled(): void
    {
        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass(TypesClass::class), new DummyParser());

        self::assertNotNull($info->source);
        self::assertSame((new \ReflectionClass(TypesClass::class))->getFileName(), $info->source?->file);
    }

    public function testReadTypesAreDerivedFromReflection(): void
    {
        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass(TypesClass::class), new DummyParser());

        self::assertSame('int', $info->properties['intProp']->read->definition);
        self::assertSame('string|null', $info->properties['nullableString']->read->definition);
        self::assertSame('string|int', $info->properties['unionProp']->read->definition);
        self::assertSame(
            '(\\TypeLang\\Mapper\\Tests\\Mapping\\Reader\\Stub\\FirstInterface&\\TypeLang\\Mapper\\Tests\\Mapping\\Reader\\Stub\\SecondInterface)',
            $info->properties['intersectionProp']->read->definition,
        );
        self::assertSame(
            '\\TypeLang\\Mapper\\Tests\\Mapping\\Reader\\Stub\\ImplementsBoth',
            $info->properties['classProp']->read->definition,
        );
    }

    public function testDefaultValueIsDetected(): void
    {
        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass(TypesClass::class), new DummyParser());

        self::assertNotNull($info->properties['withDefault']->default);
        self::assertSame('d', $info->properties['withDefault']->default?->value);
    }

    #[RequiresPhp(versionRequirement: '>=8.4')]
    public function testHookSourceAndWriteTypeFromSetter(): void
    {
        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass(TypesHooksClass::class), new DummyParser());

        self::assertSame('string', $info->properties['withGetHook']->read->definition);
        self::assertNotNull($info->properties['withGetHook']->read->source);

        self::assertSame('int', $info->properties['withSetHook']->read->definition);
        self::assertNotNull($info->properties['withSetHook']->write->source);

        self::assertSame('int|null', $info->properties['withSetHook']->write->definition);
        self::assertNotNull($info->properties['withSetHook']->write->source);
    }
}
