<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhp;
use TypeLang\Mapper\Mapping\Reader\NullReader;
use TypeLang\Mapper\Mapping\Reader\ReflectionReader;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\SampleClass;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\TypesClass;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\TypesHooksClass;

#[Group('reader')]
final class ReflectionReaderTest extends ReaderTestCase
{
    public function testReadsPublicPropertiesOnly(): void
    {
        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass(SampleClass::class));

        self::assertArrayHasKey('name', $info->properties);
        self::assertArrayHasKey('age', $info->properties);
        self::assertCount(2, $info->properties);
    }

    public function testPropertyNamesAreCorrect(): void
    {
        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass(SampleClass::class));

        self::assertSame('name', $info->properties['name']->name);
        self::assertSame('age', $info->properties['age']->name);
    }

    public function testExcludesProtectedProperties(): void
    {
        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass(TypesClass::class));

        self::assertArrayNotHasKey('protectedProp', $info->properties);
    }

    public function testExcludesPrivateProperties(): void
    {
        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass(TypesClass::class));

        self::assertArrayNotHasKey('privateProp', $info->properties);
    }

    public function testExcludesStaticProperties(): void
    {
        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass(TypesClass::class));

        self::assertArrayNotHasKey('staticProp', $info->properties);
    }

    public function testClassSourceIsPopulated(): void
    {
        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass(TypesClass::class));

        self::assertNotNull($info->source);
        self::assertSame((new \ReflectionClass(TypesClass::class))->getFileName(), $info->source->file);
        self::assertIsInt($info->source->line);
        self::assertGreaterThan(0, $info->source->line);
    }

    public function testReadsSimpleTypeCorrectly(): void
    {
        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass(TypesClass::class));

        self::assertSame('int', $info->properties['intProp']->read->definition);
    }

    public function testReadsNullableTypeCorrectly(): void
    {
        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass(TypesClass::class));

        self::assertSame('string|null', $info->properties['nullableString']->read->definition);
    }

    public function testReadsUnionTypeCorrectly(): void
    {
        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass(TypesClass::class));

        self::assertSame('string|int', $info->properties['unionProp']->read->definition);
    }

    public function testReadsIntersectionTypeCorrectly(): void
    {
        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass(TypesClass::class));

        self::assertSame(
            '(\\TypeLang\\Mapper\\Tests\\Mapping\\Reader\\Stub\\FirstInterface&\\TypeLang\\Mapper\\Tests\\Mapping\\Reader\\Stub\\SecondInterface)',
            $info->properties['intersectionProp']->read->definition,
        );
    }

    public function testReadsClassTypeWithFullyQualifiedName(): void
    {
        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass(TypesClass::class));

        self::assertSame(
            '\\TypeLang\\Mapper\\Tests\\Mapping\\Reader\\Stub\\ImplementsBoth',
            $info->properties['classProp']->read->definition,
        );
    }

    public function testDefaultValueIsDetectedForRegularProperty(): void
    {
        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass(TypesClass::class));

        self::assertNotNull($info->properties['withDefault']->default);
        self::assertSame('d', $info->properties['withDefault']->default->value);
    }

    public function testNoDefaultValueForPropertyWithoutDefault(): void
    {
        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass(TypesClass::class));

        self::assertNull($info->properties['intProp']->default);
    }

    public function testReadTypeMatchesWriteTypeByDefault(): void
    {
        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass(TypesClass::class));

        self::assertSame(
            $info->properties['intProp']->read->definition,
            $info->properties['intProp']->write->definition
        );
    }

    public function testAcceptsCustomDelegate(): void
    {
        $delegate = new NullReader();
        $reader = new ReflectionReader($delegate);

        $info = $reader->read(new \ReflectionClass(TypesClass::class));

        self::assertNotNull($info);
    }

    public function testWorksWithEmptyClass(): void
    {
        $emptyClass = new class {};
        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass($emptyClass));

        self::assertCount(0, $info->properties);
        self::assertNotNull($info->source);
    }

    public function testHandlesMixedTypeProperty(): void
    {
        $testClass = new class {
            public mixed $mixedProp;
        };

        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass($testClass));

        self::assertArrayHasKey('mixedProp', $info->properties);
        self::assertSame('mixed', $info->properties['mixedProp']->read->definition);
    }

    public function testHandlesUntypedProperty(): void
    {
        $testClass = new class {
            public $untypedProp;
        };

        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass($testClass));

        self::assertArrayHasKey('untypedProp', $info->properties);
        self::assertSame('mixed', $info->properties['untypedProp']->read->definition);
    }

    public function testHandlesBuiltinTypes(): void
    {
        $testClass = new class {
            public int $intProp;
            public string $stringProp;
            public bool $boolProp;
            public float $floatProp;
            public array $arrayProp;
        };

        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass($testClass));

        self::assertSame('int', $info->properties['intProp']->read->definition);
        self::assertSame('string', $info->properties['stringProp']->read->definition);
        self::assertSame('bool', $info->properties['boolProp']->read->definition);
        self::assertSame('float', $info->properties['floatProp']->read->definition);
        self::assertSame('array', $info->properties['arrayProp']->read->definition);
    }

    public function testHandlesPromotedPropertyDefaultValue(): void
    {
        $testClass = new class ('default') {
            public function __construct(
                public string $promoted = 'default'
            ) {}
        };

        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass($testClass));

        self::assertNotNull($info->properties['promoted']->default);
        self::assertSame('default', $info->properties['promoted']->default->value);
    }

    public function testHandlesPromotedPropertyWithoutDefaultValue(): void
    {
        $testClass = new class ('value') {
            public function __construct(
                public string $promoted
            ) {}
        };

        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass($testClass));

        self::assertNull($info->properties['promoted']->default);
    }

    public function testHandlesComplexDefaultValues(): void
    {
        $testClass = new class {
            public array $arrayDefault = [1, 2, 3];
            public ?string $nullDefault = null;
            public bool $boolDefault = true;
            public int $intDefault = 42;
            public float $floatDefault = 3.14;
        };

        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass($testClass));

        self::assertSame([1, 2, 3], $info->properties['arrayDefault']->default->value);
        self::assertNull($info->properties['nullDefault']->default->value);
        self::assertTrue($info->properties['boolDefault']->default->value);
        self::assertSame(42, $info->properties['intDefault']->default->value);
        self::assertSame(3.14, $info->properties['floatDefault']->default->value);
    }

    #[RequiresPhp(versionRequirement: '>=8.4')]
    public function testReadsGetHookSourceInfo(): void
    {
        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass(TypesHooksClass::class));

        self::assertNotNull($info->properties['withGetHook']->read->source);
        self::assertSame('string', $info->properties['withGetHook']->read->definition);
    }

    #[RequiresPhp(versionRequirement: '>=8.4')]
    public function testReadsSetHookSourceInfo(): void
    {
        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass(TypesHooksClass::class));

        self::assertNotNull($info->properties['withSetHook']->write->source);
    }

    #[RequiresPhp(versionRequirement: '>=8.4')]
    public function testReadsSetHookWriteType(): void
    {
        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass(TypesHooksClass::class));

        self::assertSame('int', $info->properties['withSetHook']->read->definition);
        self::assertSame('int|null', $info->properties['withSetHook']->write->definition);
    }

    #[RequiresPhp(versionRequirement: '>=8.4')]
    public function testPropertyWithoutSetHookHasNoWriteTypeOverride(): void
    {
        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass(TypesHooksClass::class));

        // Property with only get hook should have read and write types equal
        self::assertSame(
            $info->properties['withGetHook']->read->definition,
            $info->properties['withGetHook']->write->definition
        );
    }

    public function testHandlesClassWithoutPublicProperties(): void
    {
        $testClass = new class {
            private int $private;
            protected string $protected;
        };

        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass($testClass));

        self::assertCount(0, $info->properties);
    }

    public function testHandlesClassWithOnlyStaticProperties(): void
    {
        $testClass = new class {
            public static int $staticProp = 10;
        };

        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass($testClass));

        self::assertCount(0, $info->properties);
    }

    public function testHandlesSelfType(): void
    {
        $testClass = new class {
            public self $selfProp;
        };

        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass($testClass));

        self::assertArrayHasKey('selfProp', $info->properties);
        // self is a special type and should be kept as-is
        self::assertStringContainsString('self', $info->properties['selfProp']->read->definition);
    }

    public function testHandlesParentType(): void
    {
        $parentClass = '__testParentClass' . \hash('xxh32', \random_bytes(32));
        $childClass = '__testChildClass' . \hash('xxh32', \random_bytes(32));

        eval(\sprintf('class %s {}', $parentClass));
        eval(\sprintf(<<<'PHP'
            class %s extends %s {
                public parent $parentProp;
            }
            PHP, $childClass, $parentClass));

        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass($childClass));

        self::assertArrayHasKey('parentProp', $info->properties);
        self::assertStringContainsString('parent', $info->properties['parentProp']->read->definition);
    }

    public function testHandlesStaticType(): void
    {
        $testClass = new class {
            public static $staticTypeProp;
        };

        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass($testClass));

        // Static properties should be excluded
        self::assertCount(0, $info->properties);
    }

    public function testHandlesComplexUnionTypes(): void
    {
        $testClass = new class {
            public string|int|float|bool $multiUnion;
        };

        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass($testClass));

        $definition = $info->properties['multiUnion']->read->definition;
        self::assertStringContainsString('string', $definition);
        self::assertStringContainsString('int', $definition);
        self::assertStringContainsString('float', $definition);
        self::assertStringContainsString('bool', $definition);
    }

    public function testHandlesNullableUnionTypes(): void
    {
        $testClass = new class {
            public string|int|null $nullableUnion;
        };

        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass($testClass));

        $definition = $info->properties['nullableUnion']->read->definition;
        self::assertStringContainsString('string', $definition);
        self::assertStringContainsString('int', $definition);
        self::assertStringContainsString('null', $definition);
    }

    public function testPropertyReadSourceIsNull(): void
    {
        $testClass = new class {
            public int $prop;
        };

        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass($testClass));

        self::assertNull($info->properties['prop']->read->source);
    }

    public function testPropertyWriteSourceIsNull(): void
    {
        $testClass = new class {
            public int $prop;
        };

        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass($testClass));

        self::assertNull($info->properties['prop']->write->source);
    }

    public function testHandlesAnonymousClass(): void
    {
        $anonymous = new class {
            public string $name = 'test';
        };

        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass($anonymous));

        self::assertArrayHasKey('name', $info->properties);
        self::assertSame('string', $info->properties['name']->read->definition);
        self::assertSame('test', $info->properties['name']->default->value);
    }

    public function testHandlesClassFromInternalNamespace(): void
    {
        $reader = new ReflectionReader();
        $info = $reader->read(new \ReflectionClass(\stdClass::class));

        // stdClass has no public properties
        self::assertCount(0, $info->properties);
    }
}
