<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Reader;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TypeLang\Mapper\Mapping\Draft\ClassDraft;
use TypeLang\Mapper\Mapping\Exception\InvalidAttributeException;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Exception\InvalidOptionValueException;
use TypeLang\Mapper\Mapping\Exception\UnknownPropertyException;
use TypeLang\Mapper\Mapping\MapAlias;
use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapName;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\Metadata\AliasConflict;
use TypeLang\Mapper\Mapping\Metadata\SkipCondition\EmptySkipCondition;
use TypeLang\Mapper\Mapping\Metadata\SkipCondition\ExpressionSkipCondition;
use TypeLang\Mapper\Mapping\Metadata\SkipCondition\NullSkipCondition;
use TypeLang\Mapper\Mapping\NormalizeAsArray;
use TypeLang\Mapper\Mapping\Reader\AttributeMetadataReader;
use TypeLang\Mapper\Mapping\Reader\ReflectionMetadataReader;
use TypeLang\Mapper\Mapping\Tests\Stub\AliasConflictCaseStub;
use TypeLang\Mapper\Mapping\Tests\Stub\AliasConstantStub;
use TypeLang\Mapper\Mapping\Tests\Stub\AliasStub;
use TypeLang\Mapper\Mapping\Tests\Stub\AttributeStub;
use TypeLang\Mapper\Mapping\Tests\Stub\BrokenAliasStub;
use TypeLang\Mapper\Mapping\Tests\Stub\BrokenAttributeStub;
use TypeLang\Mapper\Mapping\Tests\Stub\BrokenDiscriminatorStub;
use TypeLang\Mapper\Mapping\Tests\Stub\BrokenNamingStub;
use TypeLang\Mapper\Mapping\Tests\Stub\BrokenPropertyAliasStub;
use TypeLang\Mapper\Mapping\Tests\Stub\BrokenSkipStub;
use TypeLang\Mapper\Mapping\Tests\Stub\Concern\OwnerTrait;
use TypeLang\Mapper\Mapping\Tests\Stub\ConflictingAliasStub;
use TypeLang\Mapper\Mapping\Tests\Stub\CustomFunctionSkipStub;
use TypeLang\Mapper\Mapping\Tests\Stub\FunctionSkipStub;
use TypeLang\Mapper\Mapping\Tests\Stub\MessageStub;
use TypeLang\Mapper\Mapping\Tests\Stub\MethodAttributeStub;
use TypeLang\Mapper\Mapping\Tests\Stub\MisplacedAttributeStub;
use TypeLang\Mapper\Mapping\Tests\Stub\NamingStub;
use TypeLang\Mapper\Mapping\Tests\Stub\PropertyAliasStub;
use TypeLang\Mapper\Mapping\Tests\Stub\RepeatedAttributeStub;
use TypeLang\Mapper\Mapping\Tests\Stub\ShapeStub;
use TypeLang\Mapper\Mapping\Tests\Stub\SkipStub;
use TypeLang\Mapper\Mapping\Tests\Stub\StatusStub;
use TypeLang\Mapper\Mapping\Tests\Stub\StrictAttributeStub;
use TypeLang\Mapper\Mapping\Tests\Stub\ThrowingNamingStrategyStub;
use TypeLang\Mapper\Mapping\Tests\Stub\ThrowingNamingStub;
use TypeLang\Mapper\Mapping\Tests\Stub\TraitUserStub;
use TypeLang\Mapper\Mapping\Tests\Stub\UnitAliasStub;
use TypeLang\Mapper\Mapping\Tests\Stub\UnparsableTypeStub;
use TypeLang\Mapper\Mapping\Tests\Stub\UnselectedAttributeStub;
use TypeLang\Mapper\Mapping\Tests\Stub\UserStub;
use TypeLang\Mapper\Mapping\Tests\Stub\WrongContextSkipStub;
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

        self::assertSame(['id', 'accounts', 'value', 'count'], \array_keys($metadata->members));
        self::assertSame('user_id', self::getProperty($metadata, 'id')->read?->name);

        $accounts = self::getProperty($metadata, 'accounts');
        self::assertType('list<' . UserStub::class . '>', $accounts->read?->type);
        self::assertType('list<' . UserStub::class . '>', $accounts->write?->type);
        self::assertSame('AttributeStub.php', \basename((string) $accounts->read?->type?->source->file));
        self::assertNull($accounts->strict);

        $value = self::getProperty($metadata, 'value');
        self::assertType('int', $value->read?->type);
        self::assertType('int', $value->write?->type);
        self::assertTrue($value->strict);
    }

    public function testNamedStrictArgumentAppliesToBothTypes(): void
    {
        $value = self::getProperty(self::readAttributes(StrictAttributeStub::class), 'value');

        self::assertType('positive-int', $value->read?->type);
        self::assertType('positive-int', $value->write?->type);
        self::assertTrue($value->strict);
    }

    public function testAttributeKeepsValuesItDoesNotSet(): void
    {
        $id = self::getProperty(self::readAttributes(AttributeStub::class), 'id');

        self::assertType('int', $id->read?->type);
        self::assertTrue($id->isOptional);
    }

    public function testAttributeOfTraitPropertyUsesImportsOfTraitFileAndSelfOfClass(): void
    {
        $type = self::getProperty(self::readAttributes(TraitUserStub::class), 'main')->read?->type;

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

        self::assertNotNull(self::getProperty($metadata, 'accounts')->read?->type?->error);
        self::assertType('positive-int', self::getProperty($metadata, 'count')->read?->type);
    }

    public function testUnparsableTypeIsTypeWithErrorAndIsNotThrown(): void
    {
        $items = self::getProperty(self::readAttributes(UnparsableTypeStub::class), 'items');

        self::assertNotNull($items->read?->type?->error);
        self::assertSame('UnparsableTypeStub.php', \basename((string) $items->read?->type?->source->file));
        self::assertSame($items->read?->type, $items->write?->type);
    }

    public function testRepeatedAttributeIsThrownOnce(): void
    {
        $class = RepeatedAttributeStub::class;
        $at = ' in RepeatedAttributeStub.php';

        self::assertSame(
            [
                \sprintf(
                    '%s::$declared: attribute "%s" cannot be created (Attribute "%2$s" must not be repeated)%s',
                    $class,
                    MapGetter::class,
                    $at,
                ),
                \sprintf(
                    '%s::$renamed: attribute "%s" cannot be created (Attribute "%2$s" must not be repeated)%s',
                    $class,
                    MapName::class,
                    $at,
                ),
            ],
            self::getErrors(static fn() => self::provide(
                $class,
                new AttributeMetadataReader(),
                new ReflectionMetadataReader(),
            )),
        );
    }

    public function testMapTypeOfMethodIsThrownAndPropertyIsStillRead(): void
    {
        $read = static fn() => self::readAttributes(MethodAttributeStub::class);
        $exceptions = self::getExceptions($read);

        self::assertCount(1, $exceptions);
        self::assertInstanceOf(InvalidAttributeException::class, $exceptions[0]);
        self::assertSame([
            \sprintf(
                '%s::getId(): attribute "%s" is not allowed on a method or a hook in MethodAttributeStub.php',
                MethodAttributeStub::class,
                MapType::class,
            ),
        ], self::getErrors($read));

        try {
            $read();
        } catch (InvalidMetadataException $e) {
            self::assertType('positive-int', self::getProperty($e->metadata, 'id')->read?->type);
        }
    }

    public function testAttributeOfPropertyThatIsNotMappedIsThrown(): void
    {
        $read = static fn() => self::readAttributes(UnselectedAttributeStub::class);
        $class = UnselectedAttributeStub::class;
        $hint = 'property is not mapped, give it "MapGetter" or "MapSetter" or choose it by the selector';

        self::assertContainsOnlyInstancesOf(UnknownPropertyException::class, self::getExceptions($read));
        self::assertSame([
            "{$class}::\$secret: {$hint} in UnselectedAttributeStub.php",
            "{$class}::\$hidden: {$hint} in UnselectedAttributeStub.php",
            "{$class}::\$counter: property is static and cannot be mapped in UnselectedAttributeStub.php",
        ], self::getErrors($read));
    }

    public function testFirstReaderMapsNoPropertiesSoEveryAttributeIsThrown(): void
    {
        $reader = new AttributeMetadataReader();
        $errors = self::getErrors(static fn() => self::readAsFirst($reader, AttributeStub::class));
        $class = AttributeStub::class;
        $hint = 'property is not mapped, give it "MapGetter" or "MapSetter" or choose it by the selector';

        self::assertSame([
            "{$class}::\$id: {$hint} in AttributeStub.php",
            "{$class}::\$accounts: {$hint} in AttributeStub.php",
            "{$class}::\$value: {$hint} in AttributeStub.php",
            "{$class}::\$count: {$hint} in AttributeStub.php",
        ], $errors);
    }

    public function testFirstReaderOfClassWithoutAttributesReturnsNoProperties(): void
    {
        $metadata = self::readAsFirst(new AttributeMetadataReader(), UserStub::class);

        self::assertSame(UserStub::class, $metadata->name);
        self::assertSame([], $metadata->members);
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
        self::assertSame(
            ['on' => 'Enabled', 'yes' => 'Enabled'],
            self::readAttributes(UnitAliasStub::class)->aliases,
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
                AliasConstantStub::class . '::A: aliases are allowed only for the cases of an enum'
                    . ' in AliasConstantStub.php',
            ],
            self::getErrors(static fn() => self::readAttributes(AliasConstantStub::class)),
        );
    }

    public function testAliasesOfPropertyAreJoinedWithLastConflict(): void
    {
        $metadata = self::readAttributes(PropertyAliasStub::class);
        $email = self::getProperty($metadata, 'email');
        $name = self::getProperty($metadata, 'name');

        self::assertSame(['mail', 'e_mail'], $email->aliases);
        self::assertSame(AliasConflict::UseFirst, $email->aliasConflict);
        self::assertSame([], $name->aliases);
        self::assertSame(AliasConflict::Fail, $name->aliasConflict);
    }

    public function testWrongAliasesOfPropertyAreThrown(): void
    {
        self::assertSame(
            [
                BrokenPropertyAliasStub::class . '::$email: value of "aliases" must be a non-empty string'
                    . ' in BrokenPropertyAliasStub.php',
            ],
            self::getErrors(static fn() => self::readAttributes(BrokenPropertyAliasStub::class)),
        );
        self::assertSame(
            [
                AliasConflictCaseStub::class . '::Enabled: alias conflict is allowed only for the aliases'
                    . ' of a property in AliasConflictCaseStub.php',
            ],
            self::getErrors(static fn() => self::readAttributes(AliasConflictCaseStub::class)),
        );
        self::assertSame(
            [
                ConflictingAliasStub::class . '::$email: repeated attribute "' . MapAlias::class . '" gives'
                    . ' different "onConflict" in ConflictingAliasStub.php',
            ],
            self::getErrors(static fn() => self::readAttributes(ConflictingAliasStub::class)),
        );
    }

    public function testAttributesOnPlacesTheirDeclarationsDoNotAllowAreThrown(): void
    {
        $class = MisplacedAttributeStub::class;
        $at = ' in MisplacedAttributeStub.php';

        self::assertSame(
            [
                \sprintf('%s: attribute "%s" is not allowed on a class%s', $class, MapName::class, $at),
                \sprintf(
                    '%s::LIMIT: attribute "%s" is not allowed on a constant or a case%s',
                    $class,
                    MapType::class,
                    $at,
                ),
                \sprintf(
                    '%s::$name: attribute "%s" is not allowed on a property%s',
                    $class,
                    NormalizeAsArray::class,
                    $at,
                ),
            ],
            self::getErrors(static fn() => self::readAttributes($class)),
        );
    }

    public function testNameOfObjectOfExpressionThatIsNotVariableNameIsThrown(): void
    {
        $class = WrongContextSkipStub::class;
        $message = '%s::$%s: value of "context" must be a variable name other than "value"'
            . ' in WrongContextSkipStub.php';

        self::assertSame(
            [\sprintf($message, $class, 'reserved'), \sprintf($message, $class, 'spaced')],
            self::getErrors(static fn() => self::readAttributes($class)),
        );
    }

    public function testNamingStrategyIsReadWithoutRenaming(): void
    {
        $metadata = self::readAttributes(NamingStub::class);

        self::assertInstanceOf(SnakeCaseNamingStrategy::class, $metadata->namingStrategy);
        self::assertSame('firstName', self::getProperty($metadata, 'firstName')->read?->name);
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
        self::assertSame(
            [
                ThrowingNamingStub::class . ': naming strategy "' . ThrowingNamingStrategyStub::class
                    . '" cannot be created (boom) in ThrowingNamingStub.php',
            ],
            self::getErrors(static fn() => self::readAttributes(ThrowingNamingStub::class)),
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
            self::assertFalse($e->metadata->normalizeAsArray);
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

    public function testFunctionsOfDefaultLanguageInExpressionAreAccepted(): void
    {
        $metadata = self::readAttributes(FunctionSkipStub::class);

        foreach (['email', 'phone', 'name'] as $name) {
            $conditions = self::getProperty($metadata, $name)->skip;

            self::assertCount(1, $conditions);
            self::assertInstanceOf(ExpressionSkipCondition::class, $conditions[0]);
        }
    }

    public function testFunctionOfGivenLanguageInExpressionIsAccepted(): void
    {
        $language = new ExpressionLanguage();
        $language->register(
            'blank',
            static fn(string $v): string => $v,
            static fn(array $v, mixed $x): bool => $x === '',
        );

        $reader = new AttributeMetadataReader(expressions: $language);
        $metadata = self::readOverReflection($reader, CustomFunctionSkipStub::class);

        self::assertCount(1, self::getProperty($metadata, 'email')->skip);
    }

    public function testUnknownFunctionInExpressionIsThrown(): void
    {
        $errors = self::getErrors(static fn() => self::readAttributes(CustomFunctionSkipStub::class));

        self::assertCount(1, $errors);
        self::assertStringStartsWith(
            CustomFunctionSkipStub::class . '::$email: expression "blank(value)" is invalid (',
            $errors[0],
        );
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
     * @return ClassDraft<T>
     * @throws \Throwable
     */
    private static function readAttributes(string $class): ClassDraft
    {
        return self::readOverReflection(new AttributeMetadataReader(), $class);
    }
}
