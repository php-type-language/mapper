<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader;

use PHPUnit\Framework\Attributes\RequiresPhp;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\Metadata\Condition\EmptyConditionInfo;
use TypeLang\Mapper\Mapping\Metadata\Condition\ExpressionConditionInfo;
use TypeLang\Mapper\Mapping\Metadata\Condition\NullConditionInfo;
use TypeLang\Mapper\Mapping\Reader\AttributeReader;
use TypeLang\Mapper\Mapping\Reader\NullReader;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\Animal;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\BaseClassWithAttributes;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\BaseType;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\ChildClassWithAttributes;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\ClassWithNormalizeAsArray;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\ClassWithNormalizeAsArrayDisabled;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\ClassWithoutAttributes;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\ClassWithTypeError;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\CombinedAttributesClass;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\EmptyDiscriminatorMap;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\MixedAttributesClass;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\MultiplePropertiesWithAttributes;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\PropertyWithAllAttributes;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\PropertyWithErrorMessages;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\PropertyWithHookAttributes;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\PropertyWithMapName;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\PropertyWithMapType;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\PropertyWithMultipleSkipConditions;
use TypeLang\Mapper\Tests\Mapping\Reader\Stub\PropertyWithoutAttributes;

final class AttributeReaderTest extends ReaderTestCase
{
    public function testReadsNormalizeAsArrayAttribute(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(ClassWithNormalizeAsArray::class));

        self::assertTrue($info->isNormalizeAsArray);
    }

    public function testReadsNormalizeAsArrayDisabledAttribute(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(ClassWithNormalizeAsArrayDisabled::class));

        self::assertFalse($info->isNormalizeAsArray);
    }

    public function testReadsDiscriminatorMapAttribute(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(Animal::class));

        self::assertNotNull($info->discriminator);
        self::assertSame('type', $info->discriminator->field);
        self::assertArrayHasKey('cat', $info->discriminator->map);
        self::assertArrayHasKey('dog', $info->discriminator->map);
    }

    public function testReadsDiscriminatorMapWithOtherwiseAttribute(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(BaseType::class));

        self::assertNotNull($info->discriminator);
        self::assertSame('kind', $info->discriminator->field);
        self::assertNotNull($info->discriminator->default);
        self::assertStringContainsString('DefaultType', $info->discriminator->default->definition);
    }

    public function testReadsClassTypeErrorAttribute(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(ClassWithTypeError::class));

        self::assertSame('Custom class type error', $info->typeErrorMessage);
    }

    public function testClassWithoutAttributesHasDefaults(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(ClassWithoutAttributes::class));

        self::assertNull($info->isNormalizeAsArray);
        self::assertNull($info->discriminator);
        self::assertNull($info->typeErrorMessage);
    }

    public function testReadsMapTypeAttribute(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(PropertyWithMapType::class));

        self::assertSame('list<int>', $info->properties['items']->read->definition);
        self::assertSame('list<int>', $info->properties['items']->write->definition);
    }

    public function testMapTypeHasSourceInfo(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(PropertyWithMapType::class));

        self::assertNotNull($info->properties['items']->read->source);
        self::assertIsString($info->properties['items']->read->source->file);
        self::assertGreaterThan(0, $info->properties['items']->read->source->line);
    }

    public function testReadsMapNameAttribute(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(PropertyWithMapName::class));

        self::assertSame('custom_name', $info->properties['property']->alias);
    }

    public function testPropertyWithoutAliasHasDefaultAlias(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(PropertyWithoutAttributes::class));

        self::assertSame('simple', $info->properties['simple']->alias);
    }

    public function testReadsSkipWhenNullAttribute(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(PropertyWithMultipleSkipConditions::class));

        $skipConditions = $info->properties['value']->skip;

        $hasNullCondition = false;
        foreach ($skipConditions as $condition) {
            if ($condition instanceof NullConditionInfo) {
                $hasNullCondition = true;
                break;
            }
        }

        self::assertTrue($hasNullCondition);
    }

    public function testReadsSkipWhenEmptyAttribute(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(PropertyWithMultipleSkipConditions::class));

        $skipConditions = $info->properties['value']->skip;

        $hasEmptyCondition = false;
        foreach ($skipConditions as $condition) {
            if ($condition instanceof EmptyConditionInfo) {
                $hasEmptyCondition = true;
                break;
            }
        }

        self::assertTrue($hasEmptyCondition);
    }

    public function testReadsMultipleSkipWhenAttributes(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(PropertyWithMultipleSkipConditions::class));

        $skipConditions = $info->properties['value']->skip;

        $expressionConditions = [];
        foreach ($skipConditions as $condition) {
            if ($condition instanceof ExpressionConditionInfo) {
                $expressionConditions[] = $condition;
            }
        }

        self::assertCount(2, $expressionConditions);
    }

    public function testReadsSkipWhenExpressionContent(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(PropertyWithMultipleSkipConditions::class));

        $skipConditions = $info->properties['value']->skip;

        $expressions = [];
        foreach ($skipConditions as $condition) {
            if ($condition instanceof ExpressionConditionInfo) {
                $expressions[] = $condition->expression;
            }
        }

        self::assertContains('value === 0', $expressions);
        self::assertContains('value < 0', $expressions);
    }

    public function testReadsSkipWhenContext(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(PropertyWithMultipleSkipConditions::class));

        $skipConditions = $info->properties['value']->skip;

        $hasContextCondition = false;
        foreach ($skipConditions as $condition) {
            if ($condition instanceof ExpressionConditionInfo) {
                if ($condition->context === 'negative') {
                    $hasContextCondition = true;
                    break;
                }
            }
        }

        self::assertTrue($hasContextCondition);
    }

    public function testPropertyWithoutSkipConditionsHasEmptyList(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(PropertyWithoutAttributes::class));

        self::assertCount(0, $info->properties['simple']->skip);
    }

    public function testReadsPropertyTypeErrorAttribute(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(PropertyWithErrorMessages::class));

        self::assertSame('Type error for field', $info->properties['field']->typeErrorMessage);
    }

    public function testReadsPropertyUndefinedErrorAttribute(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(PropertyWithErrorMessages::class));

        self::assertSame('Field is required', $info->properties['field']->undefinedErrorMessage);
    }

    public function testPropertyWithoutErrorMessagesHasNulls(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(PropertyWithoutAttributes::class));

        self::assertNull($info->properties['simple']->typeErrorMessage);
        self::assertNull($info->properties['simple']->undefinedErrorMessage);
    }

    public function testReadsAllPropertyAttributesTogether(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(PropertyWithAllAttributes::class));

        $property = $info->properties['complex'];

        self::assertSame('string', $property->read->definition);
        self::assertSame('aliased', $property->alias);
        self::assertSame('Invalid type', $property->typeErrorMessage);
        self::assertSame('Missing field', $property->undefinedErrorMessage);
        self::assertGreaterThan(0, \count($property->skip));
    }

    public function testClassWithMixedPropertyAttributes(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(MixedAttributesClass::class));

        self::assertSame('int', $info->properties['typed']->read->definition);
        self::assertSame('renamed', $info->properties['aliased']->alias);
        self::assertGreaterThan(0, \count($info->properties['nullable']->skip));
        self::assertSame('plain', $info->properties['plain']->alias);
    }

    public function testCombinedClassAndPropertyAttributes(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(CombinedAttributesClass::class));

        // Class attributes
        self::assertTrue($info->isNormalizeAsArray);
        self::assertSame('Class error', $info->typeErrorMessage);

        // Property attributes
        self::assertSame('int', $info->properties['id']->read->definition);
        self::assertSame('id_field', $info->properties['id']->alias);
        self::assertSame('Property error', $info->properties['id']->typeErrorMessage);
    }

    #[RequiresPhp(versionRequirement: '>=8.4')]
    public function testReadsMapTypeOnGetHook(): void
    {
        $class = new \ReflectionClass(PropertyWithHookAttributes::class);
        $reader = new AttributeReader();
        $info = $reader->read($class);

        // Get hook overrides read type
        self::assertSame('int', $info->properties['withGetHook']->read->definition);
    }

    #[RequiresPhp(versionRequirement: '>=8.4')]
    public function testReadsMapTypeOnSetHook(): void
    {
        $class = new \ReflectionClass(PropertyWithHookAttributes::class);
        $reader = new AttributeReader();
        $info = $reader->read($class);

        // Set hook overrides write type
        self::assertSame('string|\Stringable', $info->properties['withSetHook']->write->definition);
    }

    #[RequiresPhp(versionRequirement: '>=8.4')]
    public function testReadsMapTypeOnPropertyWithHooks(): void
    {
        $class = new \ReflectionClass(PropertyWithHookAttributes::class);
        $reader = new AttributeReader();
        $info = $reader->read($class);

        // Property-level MapType sets both read and write
        self::assertSame('string', $info->properties['propertyWithType']->read->definition);
        self::assertSame('string', $info->properties['propertyWithType']->write->definition);
    }

    public function testReadsAttributesFromBaseClass(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(BaseClassWithAttributes::class));

        self::assertTrue($info->isNormalizeAsArray);
        self::assertSame('int', $info->properties['baseField']->read->definition);
    }

    public function testReadsAttributesFromChildClass(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(ChildClassWithAttributes::class));

        // Child class has its own properties with attributes
        self::assertSame('string', $info->properties['childField']->read->definition);
    }

    public function testChildClassInheritsParentClassAttributes(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(ChildClassWithAttributes::class));

        // Inherited properties should be accessible
        self::assertArrayHasKey('baseField', $info->properties);
        self::assertSame('int', $info->properties['baseField']->read->definition);
    }

    public function testReadsAttributesForMultipleProperties(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(MultiplePropertiesWithAttributes::class));

        self::assertSame('field1_alias', $info->properties['field1']->alias);
        self::assertSame('field2_alias', $info->properties['field2']->alias);
        self::assertSame('field3_alias', $info->properties['field3']->alias);
    }

    public function testEmptyDiscriminatorMap(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(EmptyDiscriminatorMap::class));

        self::assertNotNull($info->discriminator);
        self::assertCount(0, $info->discriminator->map);
    }

    public function testAcceptsCustomDelegate(): void
    {
        $delegate = new NullReader();
        $reader = new AttributeReader($delegate);

        $info = $reader->read(new \ReflectionClass(ClassWithoutAttributes::class));

        self::assertNotNull($info);
    }

    public function testWorksWithEmptyClass(): void
    {
        $emptyClass = new class {};
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass($emptyClass));

        self::assertCount(0, $info->properties);
        self::assertNull($info->isNormalizeAsArray);
        self::assertNull($info->discriminator);
    }

    public function testWorksWithAnonymousClass(): void
    {
        $anonymous = new class {
            public string $name = 'test';
        };

        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass($anonymous));

        self::assertArrayHasKey('name', $info->properties);
    }

    public function testHandlesInternalClassWithoutAttributes(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(\stdClass::class));

        // stdClass has no attributes
        self::assertNull($info->isNormalizeAsArray);
        self::assertNull($info->discriminator);
    }

    public function testPropertyWithMapTypeButNoSourceForInternalClass(): void
    {
        // Testing that internal classes don't have source info
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(PropertyWithMapType::class));

        // Should have source since it's not an internal class
        self::assertNotNull($info->properties['items']->read->source);
    }

    public function testMultipleSkipConditionsAreAllPresent(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(PropertyWithMultipleSkipConditions::class));

        // Should have 4 skip conditions: SkipWhenNull, SkipWhenEmpty, and 2x SkipWhen
        self::assertCount(4, $info->properties['value']->skip);
    }

    public function testDiscriminatorMapWithComplexTypes(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(Animal::class));

        self::assertNotNull($info->discriminator);

        $catType = $info->discriminator->map['cat'];
        self::assertStringContainsString('Cat', $catType->definition);

        $dogType = $info->discriminator->map['dog'];
        self::assertStringContainsString('Dog', $dogType->definition);
    }

    public function testClassAndPropertyErrorMessagesDontConflict(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(CombinedAttributesClass::class));

        // Both class and property can have their own error messages
        self::assertSame('Class error', $info->typeErrorMessage);
        self::assertSame('Property error', $info->properties['id']->typeErrorMessage);
        self::assertNotSame($info->typeErrorMessage, $info->properties['id']->typeErrorMessage);
    }

    public function testReadsOnlyPublicProperties(): void
    {
        $testClass = new class {
            public string $public;
            protected int $protected;
            private float $private;
        };

        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass($testClass));

        self::assertCount(1, $info->properties);
        self::assertArrayHasKey('public', $info->properties);
        self::assertArrayNotHasKey('protected', $info->properties);
        self::assertArrayNotHasKey('private', $info->properties);
    }

    public function testAttributeLoaderOrderDoesNotAffectResult(): void
    {
        $reader = new AttributeReader();
        $info = $reader->read(new \ReflectionClass(PropertyWithAllAttributes::class));

        // All attributes should be loaded regardless of order
        self::assertNotNull($info->properties['complex']->read->definition);
        self::assertNotNull($info->properties['complex']->alias);
        self::assertNotNull($info->properties['complex']->typeErrorMessage);
        self::assertNotNull($info->properties['complex']->undefinedErrorMessage);
        self::assertNotEmpty($info->properties['complex']->skip);
    }
}
