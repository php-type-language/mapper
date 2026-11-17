<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Reader;

use TypeLang\Mapper\Mapping\Exception\InvalidAttributeException;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Exception\InvalidOptionValueException;
use TypeLang\Mapper\Mapping\Exception\UnknownPropertyException;
use TypeLang\Mapper\Mapping\MapName;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\SkipCondition\EmptySkipCondition;
use TypeLang\Mapper\Mapping\Metadata\SkipCondition\ExpressionSkipCondition;
use TypeLang\Mapper\Mapping\Metadata\SkipCondition\NullSkipCondition;
use TypeLang\Mapper\Mapping\Reader\AttributeMetadataReader;
use TypeLang\Mapper\Mapping\Tests\Stub\AliasConstantStub;
use TypeLang\Mapper\Mapping\Tests\Stub\AliasStub;
use TypeLang\Mapper\Mapping\Tests\Stub\AttributeStub;
use TypeLang\Mapper\Mapping\Tests\Stub\BrokenAliasStub;
use TypeLang\Mapper\Mapping\Tests\Stub\BrokenAttributeStub;
use TypeLang\Mapper\Mapping\Tests\Stub\BrokenDiscriminatorStub;
use TypeLang\Mapper\Mapping\Tests\Stub\BrokenNamingStub;
use TypeLang\Mapper\Mapping\Tests\Stub\BrokenSkipStub;
use TypeLang\Mapper\Mapping\Tests\Stub\Concern\OwnerTrait;
use TypeLang\Mapper\Mapping\Tests\Stub\MessageStub;
use TypeLang\Mapper\Mapping\Tests\Stub\MethodAttributeStub;
use TypeLang\Mapper\Mapping\Tests\Stub\NamingStub;
use TypeLang\Mapper\Mapping\Tests\Stub\ShapeStub;
use TypeLang\Mapper\Mapping\Tests\Stub\SkipStub;
use TypeLang\Mapper\Mapping\Tests\Stub\StatusStub;
use TypeLang\Mapper\Mapping\Tests\Stub\StrictAttributeStub;
use TypeLang\Mapper\Mapping\Tests\Stub\TraitUserStub;
use TypeLang\Mapper\Mapping\Tests\Stub\UnparsableTypeStub;
use TypeLang\Mapper\Mapping\Tests\Stub\UnselectedAttributeStub;
use TypeLang\Mapper\Mapping\Tests\Stub\UserStub;
use TypeLang\Mapper\Mapping\Tests\TestCase;
use TypeLang\Mapper\Naming\NamingStrategyInterface;
use TypeLang\Mapper\Naming\SnakeCaseNamingStrategy;
use TypeLang\Parser\TypeParser;
use TypeLang\Parser\TypeParserFeatures;

final class AttributeMetadataReaderTest extends TestCase
{
    public function testMapNameAndMapTypeSetNameTypesAndStrictness(): void
    {
        $metadata = self::readAttributes(AttributeStub::class);

        self::assertSame(['id', 'accounts', 'value', 'count'], \array_keys($metadata->properties));
        self::assertSame('user_id', self::getProperty($metadata, 'id')->externalName);

        $accounts = self::getProperty($metadata, 'accounts');
        self::assertType('list<' . UserStub::class . '>', $accounts->read);
        self::assertType('list<' . UserStub::class . '>', $accounts->write);
        self::assertSame('AttributeStub.php', \basename((string) $accounts->read->source->file));
        self::assertNull($accounts->read->isStrict);

        $value = self::getProperty($metadata, 'value');
        self::assertType('int', $value->read);
        self::assertType('int', $value->write);
        self::assertTrue($value->read->isStrict);
        self::assertTrue($value->write->isStrict);
    }

    public function testNamedStrictArgumentAppliesToBothTypes(): void
    {
        $value = self::getProperty(self::readAttributes(StrictAttributeStub::class), 'value');

        self::assertType('positive-int', $value->read);
        self::assertType('positive-int', $value->write);
        self::assertTrue($value->read->isStrict);
        self::assertTrue($value->write->isStrict);
    }

    public function testAttributeKeepsValuesItDoesNotSet(): void
    {
        $id = self::getProperty(self::readAttributes(AttributeStub::class), 'id');

        self::assertType('int', $id->read);
        self::assertSame(0, $id->default?->value);
    }

    public function testAttributeOfTraitPropertyUsesImportsOfTraitFileAndSelfOfClass(): void
    {
        $type = self::getProperty(self::readAttributes(TraitUserStub::class), 'main')->read;

        self::assertType(UserStub::class . '|' . TraitUserStub::class, $type);
        self::assertSame('OwnerTrait.php', \basename((string) $type->source->file));
    }

    public function testBrokenAttributesAreThrownTogether(): void
    {
        $read = static fn() => self::readAttributes(BrokenAttributeStub::class);
        $exceptions = self::getExceptions($read);
        $errors = self::getErrors($read);

        self::assertCount(2, $errors);
        self::assertInstanceOf(InvalidAttributeException::class, $exceptions[0]);
        self::assertInstanceOf(\Throwable::class, $exceptions[0]->getPrevious());
        self::assertStringStartsWith(
            \sprintf('%s::$id: attribute "%s" cannot be created (', BrokenAttributeStub::class, MapName::class),
            $errors[0],
        );
        self::assertInstanceOf(InvalidOptionValueException::class, $exceptions[1]);
        self::assertSame(
            \sprintf(
                '%s::$name: value of "name" must be a non-empty string in BrokenAttributeStub.php',
                BrokenAttributeStub::class,
            ),
            $errors[1],
        );
    }

    public function testTypesAreParsedByGivenParser(): void
    {
        $reader = new AttributeMetadataReader(new TypeParser(new TypeParserFeatures(generics: false)));

        $metadata = self::readOverReflection($reader, AttributeStub::class);

        self::assertNotNull(self::getProperty($metadata, 'accounts')->read?->error);
        self::assertType('positive-int', self::getProperty($metadata, 'count')->read);
    }

    public function testUnparsableTypeIsTypeWithErrorAndIsNotThrown(): void
    {
        $items = self::getProperty(self::readAttributes(UnparsableTypeStub::class), 'items');

        self::assertNotNull($items->read?->error);
        self::assertSame('UnparsableTypeStub.php', \basename((string) $items->read->source->file));
        self::assertSame($items->read, $items->write);
    }

    public function testMapTypeOfMethodIsThrownAndPropertyIsStillRead(): void
    {
        $read = static fn() => self::readAttributes(MethodAttributeStub::class);
        $exceptions = self::getExceptions($read);

        self::assertCount(1, $exceptions);
        self::assertInstanceOf(InvalidAttributeException::class, $exceptions[0]);
        self::assertSame([
            \sprintf(
                '%s::getId(): attribute "%s" is allowed only on a property or its hook in MethodAttributeStub.php',
                MethodAttributeStub::class,
                MapType::class,
            ),
        ], self::getErrors($read));

        try {
            $read();
        } catch (InvalidMetadataException $e) {
            self::assertType('positive-int', self::getProperty($e->metadata, 'id')->read);
        }
    }

    public function testAttributeOfPropertyThatIsNotMappedIsThrown(): void
    {
        $read = static fn() => self::readAttributes(UnselectedAttributeStub::class);
        $class = UnselectedAttributeStub::class;

        self::assertContainsOnlyInstancesOf(UnknownPropertyException::class, self::getExceptions($read));
        self::assertSame([
            "{$class}::\$secret: property is not mapped in UnselectedAttributeStub.php",
            "{$class}::\$hidden: property is not mapped in UnselectedAttributeStub.php",
            "{$class}::\$counter: property is not mapped in UnselectedAttributeStub.php",
        ], self::getErrors($read));
    }

    public function testFirstReaderMapsNoPropertiesSoEveryAttributeIsThrown(): void
    {
        $reader = new AttributeMetadataReader();
        $errors = self::getErrors(static fn() => self::readAsFirst($reader, AttributeStub::class));
        $class = AttributeStub::class;

        self::assertSame([
            "{$class}::\$id: property is not mapped in AttributeStub.php",
            "{$class}::\$accounts: property is not mapped in AttributeStub.php",
            "{$class}::\$value: property is not mapped in AttributeStub.php",
            "{$class}::\$count: property is not mapped in AttributeStub.php",
        ], $errors);
    }

    public function testFirstReaderOfClassWithoutAttributesReturnsNoProperties(): void
    {
        $metadata = self::readAsFirst(new AttributeMetadataReader(), UserStub::class);

        self::assertSame(UserStub::class, $metadata->name);
        self::assertSame([], $metadata->properties);
    }

    public function testDiscriminatorMapSetsPolymorphismWithTypesResolvedByFile(): void
    {
        $discriminator = self::readAttributes(ShapeStub::class)->discriminator;

        self::assertNotNull($discriminator);
        self::assertSame('type', $discriminator->field);
        self::assertSame(['user', 'status'], \array_keys($discriminator->map));
        self::assertType(UserStub::class, $discriminator->map['user']);
        self::assertType(StatusStub::class, $discriminator->map['status']);
        self::assertType(OwnerTrait::class, $discriminator->default);
        self::assertSame('ShapeStub.php', \basename((string) $discriminator->source->file));
    }

    public function testSkipAttributesSetConditionsWithParsedExpressions(): void
    {
        $metadata = self::readAttributes(SkipStub::class);
        $object = new SkipStub();

        $nickname = self::getProperty($metadata, 'nickname')->skip;
        self::assertCount(1, $nickname);
        self::assertInstanceOf(NullSkipCondition::class, $nickname[0]);

        $tags = self::getProperty($metadata, 'tags')->skip;
        self::assertCount(1, $tags);
        self::assertInstanceOf(EmptySkipCondition::class, $tags[0]);

        $email = self::getProperty($metadata, 'email')->skip;
        self::assertCount(2, $email);
        self::assertContainsOnlyInstancesOf(ExpressionSkipCondition::class, $email);
        self::assertSame('owner', $email[1]->variable);

        self::assertFalse($email[0]->isMatched($object, ''));
        self::assertTrue($email[1]->isMatched($object, ''));

        $object->isPrivate = true;
        self::assertTrue($email[0]->isMatched($object, ''));

        self::assertSame([], self::getProperty($metadata, 'isPrivate')->skip);
    }

    public function testAliasesOfCasesAreRead(): void
    {
        self::assertSame(
            ['on' => 'enabled', 'active' => 'enabled', 'enabled_v1' => 'enabled'],
            self::readAttributes(AliasStub::class)->aliases,
        );
    }

    public function testWrongAliasesAreThrown(): void
    {
        $case = BrokenAliasStub::class . '::';
        $place = ' in BrokenAliasStub.php';

        self::assertSame(
            [
                $case . 'A: alias "b" is already a value of a case' . $place,
                $case . 'B: alias 1 must be of type "string"' . $place,
                $case . 'B: alias "x" is given to more than one case' . $place,
            ],
            self::getErrors(static fn() => self::readAttributes(BrokenAliasStub::class)),
        );
        self::assertSame(
            [
                AliasConstantStub::class . '::A: aliases are allowed only for the cases of a backed enum'
                    . ' in AliasConstantStub.php',
            ],
            self::getErrors(static fn() => self::readAttributes(AliasConstantStub::class)),
        );
    }

    public function testNamingStrategyIsReadWithoutRenaming(): void
    {
        $metadata = self::readAttributes(NamingStub::class);

        self::assertInstanceOf(SnakeCaseNamingStrategy::class, $metadata->namingStrategy);
        self::assertSame('firstName', self::getProperty($metadata, 'firstName')->externalName);
    }

    public function testUnknownNamingStrategyIsThrown(): void
    {
        self::assertSame(
            [
                BrokenNamingStub::class . ': naming strategy "Snake_Case" is neither a built-in one nor an'
                    . ' instantiable class of "' . NamingStrategyInterface::class . '" in BrokenNamingStub.php',
            ],
            self::getErrors(static fn() => self::readAttributes(BrokenNamingStub::class)),
        );
    }

    public function testMessageAttributesAndFormOfObjectsAreRead(): void
    {
        $class = MessageStub::class;
        $read = static fn() => self::readAttributes(MessageStub::class);

        self::assertSame(
            ["{$class}::\$name: value of \"message\" must be a non-empty string in MessageStub.php"],
            self::getErrors($read),
        );

        try {
            $read();
        } catch (InvalidMetadataException $e) {
            $age = self::getProperty($e->metadata, 'age');

            self::assertSame('Age must be a number', $age->typeErrorMessage);
            self::assertSame('Age is required', $age->undefinedErrorMessage);
            self::assertNull(self::getProperty($e->metadata, 'name')->typeErrorMessage);
            self::assertSame('Person is expected', $e->metadata->typeErrorMessage);
            self::assertFalse($e->metadata->isNormalizeAsArray);
        }
    }

    public function testExpressionThatCannotBeParsedIsThrown(): void
    {
        $errors = self::getErrors(static fn() => self::readAttributes(BrokenSkipStub::class));

        self::assertCount(1, $errors);
        self::assertStringStartsWith(
            BrokenSkipStub::class . '::$email: expression "this." is invalid (',
            $errors[0],
        );
        self::assertStringEndsWith(' in BrokenSkipStub.php', $errors[0]);
    }

    public function testBrokenDiscriminatorMapIsThrown(): void
    {
        $class = BrokenDiscriminatorStub::class;

        self::assertSame(
            ["{$class}: value of \"field\" must be a non-empty string in BrokenDiscriminatorStub.php"],
            self::getErrors(static fn() => self::readAttributes(BrokenDiscriminatorStub::class)),
        );
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return ClassMetadata<T>
     * @throws \Throwable
     */
    private static function readAttributes(string $class): ClassMetadata
    {
        return self::readOverReflection(new AttributeMetadataReader(), $class);
    }
}
