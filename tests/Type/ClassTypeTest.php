<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhp;
use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Exception\InvalidValueException;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Instantiator\Exception\ClassNotInstantiableException;
use TypeLang\Mapper\Instantiator\InstantiatorInterface;
use TypeLang\Mapper\Instantiator\Strategy\InstantiationStrategyInterface;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\Provider\MetadataProviderInterface;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyNotWritableException;
use TypeLang\Mapper\PropertyAccessor\PropertyReaderInterface;
use TypeLang\Mapper\PropertyAccessor\PropertyWriterInterface;
use TypeLang\Mapper\PropertyAccessor\Strategy\AccessStrategyInterface;
use TypeLang\Mapper\Tests\Stub\AbstractShapeStub;
use TypeLang\Mapper\Tests\Stub\AddressStub;
use TypeLang\Mapper\Tests\Stub\AliasedStub;
use TypeLang\Mapper\Tests\Stub\BrokenSkipStub;
use TypeLang\Mapper\Tests\Stub\ComputedStub;
use TypeLang\Mapper\Tests\Stub\FirstInDataAliasStub;
use TypeLang\Mapper\Tests\Stub\HookStub;
use TypeLang\Mapper\Tests\Stub\LastInDataAliasStub;
use TypeLang\Mapper\Tests\Stub\MessageOwnerStub;
use TypeLang\Mapper\Tests\Stub\MessageStub;
use TypeLang\Mapper\Tests\Stub\MismatchedTypeStub;
use TypeLang\Mapper\Tests\Stub\NameOrFirstAliasStub;
use TypeLang\Mapper\Tests\Stub\NameOrLastAliasStub;
use TypeLang\Mapper\Tests\Stub\NodeStub;
use TypeLang\Mapper\Tests\Stub\ObjectFormStub;
use TypeLang\Mapper\Tests\Stub\ReadonlyChildStub;
use TypeLang\Mapper\Tests\Stub\ReadonlyStub;
use TypeLang\Mapper\Tests\Stub\RelativeNameStub;
use TypeLang\Mapper\Tests\Stub\RenamedStub;
use TypeLang\Mapper\Tests\Stub\SkipStub;
use TypeLang\Mapper\Tests\Stub\ThrowingHookStub;
use TypeLang\Mapper\Tests\Stub\StdClassChildStub;
use TypeLang\Mapper\Tests\Stub\StrictnessStub;
use TypeLang\Mapper\Tests\Stub\UntypedStub;
use TypeLang\Mapper\Tests\Stub\UserStub;
use TypeLang\Mapper\Tests\TestCase;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;

final class ClassTypeTest extends TestCase
{
    public function testDenormalizesObjectFromArray(): void
    {
        self::assertDenormalizes(
            UserStub::class,
            ['id' => '7', 'name' => 'John', 'address' => ['city' => 'Paris'], 'code' => 'A1'],
            self::createUser(id: 7, name: 'John', address: self::createAddress('Paris'), code: 'A1'),
        );
    }

    public function testDenormalizesObjectFromStdClass(): void
    {
        self::assertDenormalizes(
            UserStub::class,
            (object) ['id' => 7, 'name' => 'John', 'address' => (object) ['city' => 'Paris'], 'code' => 5],
            self::createUser(id: 7, name: 'John', address: self::createAddress('Paris'), code: 5),
        );
    }

    public function testStrictContextAcceptsStdClass(): void
    {
        self::assertDenormalizes(
            UserStub::class,
            (object) ['id' => 7, 'name' => 'John', 'address' => (object) ['city' => 'Paris'], 'code' => 5],
            self::createUser(id: 7, name: 'John', address: self::createAddress('Paris'), code: 5),
            self::createMapper(strict: true),
        );
    }

    public function testRejectsSubclassOfStdClass(): void
    {
        $input = new StdClassChildStub();
        $input->city = 'Paris';

        self::assertDenormalizationFails(
            AddressStub::class,
            $input,
            '$: Value must be of type object{city: string}, but object given',
        );
    }

    /**
     * @return iterable<non-empty-string, array{class-string}>
     */
    public static function internalClassDataProvider(): iterable
    {
        yield 'ArrayObject' => [\ArrayObject::class];
        yield 'Closure' => [\Closure::class];
    }

    #[DataProvider('internalClassDataProvider')]
    public function testInternalClassIsNotSupported(string $class): void
    {
        $error = self::captureBuildError($class);

        self::assertInstanceOf(TypeDefinitionException::class, $error);
        self::assertSame(\sprintf('Type "%s" is not supported', $class), $error->getMessage());
    }

    /**
     * @return iterable<non-empty-string, array{class-string, non-empty-string}>
     */
    public static function notInstantiableClassDataProvider(): iterable
    {
        yield 'abstract class' => [AbstractShapeStub::class, 'Cannot instantiate abstract class "%s"'];
    }

    /**
     * @param class-string $class
     * @param non-empty-string $reason
     */
    #[DataProvider('notInstantiableClassDataProvider')]
    public function testClassRejectedByInstantiatorIsErrorOfType(string $class, string $reason): void
    {
        $error = self::captureBuildError($class);

        self::assertInstanceOf(TypeDefinitionException::class, $error);
        self::assertInstanceOf(ClassNotInstantiableException::class, $error->getPrevious());
        self::assertSame(
            \sprintf('Class "%s" cannot be mapped: ' . $reason, $class, $class),
            $error->getMessage(),
        );
    }

    public function testInstantiatorOfUserBuildsTypeOfAbstractClass(): void
    {
        $strategy = new class implements InstantiationStrategyInterface {
            public function tryCreateInstantiator(
                \ReflectionClass $class,
                ClassMetadata $metadata,
            ): ?InstantiatorInterface {
                if ($class->name !== AbstractShapeStub::class) {
                    return null;
                }

                return new class implements InstantiatorInterface {
                    public function instantiate(array $data = []): object
                    {
                        return new class extends AbstractShapeStub {};
                    }
                };
            }
        };

        $shape = self::createMapper(instantiators: [$strategy])
            ->denormalize(['name' => 'circle'], AbstractShapeStub::class);

        self::assertInstanceOf(AbstractShapeStub::class, $shape);
        self::assertSame('circle', $shape->name);
    }

    public function testStrategyRejectingNestedClassIsErrorOfType(): void
    {
        $strategy = new class implements InstantiationStrategyInterface {
            public function tryCreateInstantiator(
                \ReflectionClass $class,
                ClassMetadata $metadata,
            ): ?InstantiatorInterface {
                if ($class->name === AddressStub::class) {
                    throw ClassNotInstantiableException::becauseClassIsAbstract($class->name);
                }

                return null;
            }
        };
        $mapper = self::createMapper(instantiators: [$strategy]);

        $error = self::captureThrowable(static fn(): mixed => $mapper->denormalize(
            ['id' => 7, 'name' => 'John', 'address' => ['city' => 'Paris'], 'code' => 5],
            UserStub::class,
        ));

        self::assertInstanceOf(TypeDefinitionException::class, $error);
        self::assertInstanceOf(ClassNotInstantiableException::class, $error->getPrevious());
        self::assertSame(
            \sprintf(
                'Class "%s" cannot be mapped: Cannot instantiate abstract class "%s"',
                AddressStub::class,
                AddressStub::class,
            ),
            $error->getMessage(),
        );
    }

    public function testTakesDefaultValueOfMissingField(): void
    {
        $user = self::createMapper()->denormalize(
            ['id' => 7, 'name' => 'John', 'address' => null, 'code' => 5],
            UserStub::class,
        );

        self::assertInstanceOf(UserStub::class, $user);
        self::assertSame('user', $user->role);
    }

    public function testRejectsMissingRequiredField(): void
    {
        self::assertDenormalizationFails(
            UserStub::class,
            ['id' => 7, 'address' => null, 'code' => 5],
            '$.name: Required field "name" is missing',
        );
    }

    public function testRejectsInvalidFieldWithItsPath(): void
    {
        self::assertDenormalizationFails(
            UserStub::class,
            ['id' => 7, 'name' => 'John', 'address' => ['city' => []], 'code' => 5],
            '$.address.city: Value must be of type string, but array(0) given',
        );
    }

    public function testRejectsValueThatIsNotArrayOrObject(): void
    {
        self::assertDenormalizationFails(
            AddressStub::class,
            'x',
            '$: Value must be of type object{city: string}, but "x" given',
        );
    }

    public function testInitializesReadonlyProperty(): void
    {
        self::assertDenormalizes(ReadonlyStub::class, ['value' => 'x'], new ReadonlyStub('x'));
    }

    public function testDenormalizesRecursiveType(): void
    {
        $tail = new NodeStub();
        $tail->name = 'b';
        $tail->next = null;

        $head = new NodeStub();
        $head->name = 'a';
        $head->next = $tail;

        self::assertDenormalizes(NodeStub::class, ['name' => 'a', 'next' => ['name' => 'b', 'next' => null]], $head);
    }

    public function testRejectsInvalidValueDeepInRecursiveType(): void
    {
        self::assertDenormalizationFails(
            NodeStub::class,
            ['name' => 'a', 'next' => ['name' => 'b', 'next' => 'x']],
            '$.next.next: Value must be of type ?object{name: string, next: ?object}, but "x" given',
        );
    }

    public function testResolvesClassNameRelativeToNamespace(): void
    {
        $expected = new RelativeNameStub();
        $expected->address = self::createAddress('Paris');

        self::assertDenormalizes(RelativeNameStub::class, ['address' => ['city' => 'Paris']], $expected);
    }

    public function testNormalizesObjectToArrayOfProperties(): void
    {
        self::assertNormalizes(
            UserStub::class,
            self::createUser(id: 7, name: 'John', address: self::createAddress('Paris'), code: 'A1'),
            ['id' => 7, 'name' => 'John', 'role' => 'user', 'address' => ['city' => 'Paris'], 'code' => 'A1'],
        );
    }

    public function testExternalNameIsKeyOfFieldInBothDirections(): void
    {
        $expected = new RenamedStub();
        $expected->email = 'a@b.c';
        $expected->name = 'John';

        self::assertDenormalizes(RenamedStub::class, ['e-mail' => 'a@b.c', 'name' => 'John'], $expected);
        self::assertNormalizes(RenamedStub::class, $expected, ['e-mail' => 'a@b.c', 'name' => 'John']);
    }

    public function testAliasIsAcceptedOnDenormalizationOnly(): void
    {
        $expected = new AliasedStub();
        $expected->email = 'a@b.c';
        $expected->name = 'John';
        $expected->age = 42;

        self::assertDenormalizes(
            AliasedStub::class,
            ['mail' => 'a@b.c', 'login' => 'John', 'years' => 42],
            $expected,
        );
        self::assertNormalizes(AliasedStub::class, $expected, ['e-mail' => 'a@b.c', 'name' => 'John', 'age' => 42]);
    }

    public function testNameFirstOrLastInDataIsTakenByUseFirstOrUseLast(): void
    {
        $first = new FirstInDataAliasStub();
        $first->email = 'x@y.z';
        $last = new LastInDataAliasStub();
        $last->email = 'a@b.c';

        self::assertDenormalizes(FirstInDataAliasStub::class, ['mail' => 'x@y.z', 'email' => 'a@b.c'], $first);
        self::assertDenormalizes(LastInDataAliasStub::class, ['mail' => 'x@y.z', 'email' => 'a@b.c'], $last);
        self::assertDenormalizes(LastInDataAliasStub::class, ['email' => 'x@y.z', 'mail' => 'a@b.c'], $last);
    }

    public function testNameIsTakenBeforeAliasesByUseNameOrFirstOrLast(): void
    {
        $data = ['mail' => 'x@y.z', 'email' => 'a@b.c', 'e_mail' => 'm@n.o'];
        $first = new NameOrFirstAliasStub();
        $first->email = 'a@b.c';
        $last = new NameOrLastAliasStub();
        $last->email = 'a@b.c';

        self::assertDenormalizes(NameOrFirstAliasStub::class, $data, $first);
        self::assertDenormalizes(NameOrLastAliasStub::class, $data, $last);

        unset($data['email']);
        $first->email = 'x@y.z';
        $last->email = 'm@n.o';

        self::assertDenormalizes(NameOrFirstAliasStub::class, $data, $first);
        self::assertDenormalizes(NameOrLastAliasStub::class, $data, $last);
    }

    public function testSeveralNamesAreViolationByFail(): void
    {
        self::assertDenormalizationFails(
            AliasedStub::class,
            ['mail' => 'a@b.c', 'email' => 'x@y.z', 'name' => 'John'],
            '$.email: Field "email" duplicates the field "mail"',
        );
    }

    public function testErrorOfAliasedFieldHasPathOfAlias(): void
    {
        self::assertDenormalizationFails(
            AliasedStub::class,
            ['mail' => 'a@b.c', 'name' => 'John', 'years' => 'old'],
            '$.years: Value must be of type int, but "old" given',
        );
        self::assertDenormalizationFails(
            AliasedStub::class,
            ['name' => 'John'],
            '$[\'e-mail\']: Required field "e-mail" is missing',
        );
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testNormalizesObjectWithComputedProperty(): void
    {
        $object = new ComputedStub();
        $object->name = 'John';

        self::assertNormalizes(ComputedStub::class, $object, ['name' => 'John', 'greeting' => 'Hello John']);
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testComputedPropertyIsIgnoredOnDenormalization(): void
    {
        $mapper = self::createMapper();

        foreach ([['name' => 'John'], ['name' => 'John', 'greeting' => 'Hi']] as $input) {
            $object = $mapper->denormalize($input, ComputedStub::class);

            self::assertInstanceOf(ComputedStub::class, $object);
            self::assertSame('Hello John', $object->greeting);
        }
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testPropertyThatCannotBeAccessedInItsDirectionIsErrorOfType(): void
    {
        $metadata = new class implements MetadataProviderInterface {
            public function read(\ReflectionClass $class): ClassMetadata
            {
                $type = TypeMetadata::createFromType(new NamedTypeNode(new Name('string')), new SourceInfo());

                return (new ClassMetadata($class->name))
                    ->withProperty(new PropertyMetadata('greeting', read: $type, write: $type));
            }
        };

        $mapper = self::createMapper(metadata: $metadata);

        $error = self::captureBuildError(ComputedStub::class, $mapper);

        self::assertInstanceOf(TypeDefinitionException::class, $error);
        self::assertInstanceOf(PropertyNotWritableException::class, $error->getPrevious());
        self::assertSame(
            \sprintf(
                'Class "%s" cannot be mapped: Property "%s::$greeting" is read-only',
                ComputedStub::class,
                ComputedStub::class,
            ),
            $error->getMessage(),
        );
    }

    public function testNormalizesRecursiveType(): void
    {
        $node = new NodeStub();
        $node->name = 'a';
        $node->next = null;

        self::assertNormalizes(NodeStub::class, $node, ['name' => 'a', 'next' => null]);
    }

    public function testNormalizationRejectsUninitializedPropertyWithItsPath(): void
    {
        $user = (new \ReflectionClass(UserStub::class))->newInstanceWithoutConstructor();
        $mapper = self::createMapper();

        $error = self::captureThrowable(static fn(): mixed => $mapper->normalize($user, UserStub::class));

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertStringStartsWith('$.id: ', $error->getMessage());
        self::assertInstanceOf(\Error::class, $error->getPrevious());
    }

    public function testStrictnessOfPropertyOverridesContext(): void
    {
        $expected = new StrictnessStub();
        $expected->strict = 1;
        $expected->loose = 2;

        self::assertDenormalizes(StrictnessStub::class, ['strict' => 1, 'loose' => '2'], $expected);
        self::assertDenormalizes(
            StrictnessStub::class,
            ['strict' => 1, 'loose' => '2'],
            $expected,
            self::createMapper(strict: true),
        );
        self::assertDenormalizationFails(
            StrictnessStub::class,
            ['strict' => '1', 'loose' => 2],
            '$.strict: Value must be of type int, but "1" given',
        );
    }

    public function testPropertyWithoutTypeIsErrorOfType(): void
    {
        $error = self::captureBuildError(UntypedStub::class);

        self::assertInstanceOf(TypeDefinitionException::class, $error);
        self::assertSame(
            \sprintf('Type of property "%s::$value" is not defined', UntypedStub::class),
            $error->getMessage(),
        );
    }

    public function testNormalizationRejectsInvalidPropertyValueWithItsPath(): void
    {
        $user = self::createUser(id: 7, name: 'John', address: null, code: 'A1');
        $user->code = 1.5;

        self::assertNormalizationFails(
            UserStub::class,
            $user,
            '$.code: Value must be of type int|string, but 1.5 given',
        );
    }

    public function testPropertiesMatchingSkipConditionsAreLeftOutOfData(): void
    {
        $object = new SkipStub();
        $object->email = 'user@example.com';
        $object->isPrivate = true;

        self::assertNormalizes(SkipStub::class, $object, ['isPrivate' => true]);
    }

    public function testPropertiesNotMatchingSkipConditionsAreNormalized(): void
    {
        $object = new SkipStub();
        $object->nickname = 'john';
        $object->tags = ['admin'];
        $object->email = 'user@example.com';

        self::assertNormalizes(SkipStub::class, $object, [
            'nickname' => 'john',
            'tags' => ['admin'],
            'email' => 'user@example.com',
            'isPrivate' => false,
        ]);
    }

    public function testFailingSkipConditionIsViolationWithPathOfProperty(): void
    {
        self::assertNormalizationFails(
            'list<' . BrokenSkipStub::class . '>',
            [new BrokenSkipStub()],
            '$[0].name: Value "John" is rejected',
        );
    }

    #[RequiresPhp('>= 8.4.0')]
    public function testThrowingGetHookIsViolationWithPathOfProperty(): void
    {
        self::assertNormalizationFails(
            'list<' . ThrowingHookStub::class . '>',
            [new ThrowingHookStub()],
            '$[0].greeting: Value null is rejected',
        );
    }

    public function testMessagesOfUserReplaceMessagesOfMapper(): void
    {
        self::assertDenormalizationFails(
            MessageStub::class,
            ['age' => 'x', 'name' => 'John'],
            '$.age: Age must be a number, "x" given',
        );
        self::assertDenormalizationFails(
            MessageStub::class,
            ['age' => 42],
            '$.name: Field name is required',
        );
        self::assertDenormalizationFails(
            MessageStub::class,
            'x',
            '$: Person is expected, "x" given',
        );
        self::assertNormalizationFails(
            MessageStub::class,
            new AddressStub(),
            '$: Person is expected, object(TypeLang\Mapper\Tests\Stub\AddressStub) given',
        );
    }

    public function testMessageOfPropertyIsKeptByEnclosingTypes(): void
    {
        self::assertDenormalizationFails(
            'list<' . MessageStub::class . '>',
            [['age' => 'x', 'name' => 'John']],
            '$[0].age: Age must be a number, "x" given',
        );
    }

    public function testClassNormalizedAsObjectGivesStdClass(): void
    {
        $mapper = self::createMapper();

        self::assertTrue($mapper->isNormalizable(new ObjectFormStub(), ObjectFormStub::class));
        self::assertEquals(
            (object) ['theme' => 'dark'],
            $mapper->normalize(new ObjectFormStub(), ObjectFormStub::class),
        );
    }

    public function testConfigurationNormalizesObjectsAsStdClass(): void
    {
        $address = self::createAddress('Paris');

        foreach ([false, true] as $isStrict) {
            $mapper = self::createMapper(config: new Configuration(strict: $isStrict, normalizeAsArray: false));

            self::assertEquals((object) ['city' => 'Paris'], $mapper->normalize($address, AddressStub::class));
        }
    }

    public function testAccessorStrategyReceivesMetadataOfProperty(): void
    {
        $strategy = new class implements AccessStrategyInterface {
            /**
             * @var list<non-empty-string>
             */
            public array $names = [];

            public function tryCreateReader(
                \ReflectionProperty $property,
                PropertyMetadata $metadata,
            ): ?PropertyReaderInterface {
                $this->names[] = $metadata->externalName;

                return null;
            }

            public function tryCreateWriter(
                \ReflectionProperty $property,
                PropertyMetadata $metadata,
            ): ?PropertyWriterInterface {
                $this->names[] = $metadata->externalName;

                return null;
            }
        };

        $object = self::createMapper(accessors: [$strategy])
            ->denormalize(['e-mail' => 'a@b.c', 'name' => 'John'], RenamedStub::class);

        self::assertSame(['e-mail', 'e-mail', 'name', 'name'], $strategy->names);
        self::assertInstanceOf(RenamedStub::class, $object);
        self::assertSame('a@b.c', $object->email);
    }

    public function testInstantiatorReceivesDataToFindObject(): void
    {
        $existing = self::createAddress('Old');

        $strategy = new class ($existing) implements InstantiationStrategyInterface {
            public function __construct(
                private readonly AddressStub $existing,
            ) {}

            public function tryCreateInstantiator(
                \ReflectionClass $class,
                ClassMetadata $metadata,
            ): ?InstantiatorInterface {
                return new class ($this->existing) implements InstantiatorInterface {
                    public function __construct(
                        private readonly AddressStub $existing,
                    ) {}

                    public function instantiate(array $data = []): object
                    {
                        if (($data['id'] ?? null) !== 1) {
                            throw new \LogicException('The object is found by the identifier of the data');
                        }

                        return $this->existing;
                    }
                };
            }
        };

        $address = self::createMapper(instantiators: [$strategy])
            ->denormalize(['id' => 1, 'city' => 'Paris'], AddressStub::class);

        self::assertSame($existing, $address);
        self::assertSame('Paris', $existing->city);
    }

    public function testHookBeforeDenormalizationChangesData(): void
    {
        $hook = new HookStub(AddressStub::class, beforeDenormalize: static function (mixed $data): mixed {
            if (\is_array($data) && \array_key_exists('town', $data)) {
                return ['city' => $data['town']];
            }

            return $data;
        });

        self::assertDenormalizes(
            AddressStub::class,
            ['town' => 'Paris'],
            self::createAddress('Paris'),
            self::createMapper(hooks: [$hook]),
        );
    }

    public function testHookAfterDenormalizationReceivesFilledObjectAndRunsOnConversionOnly(): void
    {
        $received = null;
        $hook = new HookStub(
            AddressStub::class,
            afterDenormalize: static function (object $object) use (&$received): void {
                $received = $object;
            },
        );

        $mapper = self::createMapper(hooks: [$hook]);

        self::assertTrue($mapper->isDenormalizable(['city' => 'Paris'], AddressStub::class));
        self::assertSame(['beforeDenormalize'], $hook->calls);

        $address = $mapper->denormalize(['city' => 'Paris'], AddressStub::class);

        self::assertSame($address, $received);
        self::assertSame(['beforeDenormalize', 'beforeDenormalize', 'afterDenormalize'], $hook->calls);
    }

    public function testHookBeforeNormalizationReplacesObject(): void
    {
        $hook = new HookStub(AddressStub::class, beforeNormalize: static function (object $object): object {
            \assert($object instanceof AddressStub);

            return self::createAddress(\strtoupper($object->city));
        });

        self::assertNormalizes(
            AddressStub::class,
            self::createAddress('Paris'),
            ['city' => 'PARIS'],
            self::createMapper(hooks: [$hook]),
        );
    }

    public function testHookAfterNormalizationChangesData(): void
    {
        $hook = new HookStub(AddressStub::class, afterNormalize: static fn(object $object, array $data): array => [
            ...$data,
            '_links' => ['self' => '/addresses/1'],
        ]);

        self::assertNormalizes(
            AddressStub::class,
            self::createAddress('Paris'),
            ['city' => 'Paris', '_links' => ['self' => '/addresses/1']],
            self::createMapper(hooks: [$hook]),
        );
    }

    public function testHookRunsForObjectsOfItsClassOnly(): void
    {
        $hook = new HookStub(AddressStub::class);
        $mapper = self::createMapper(hooks: [$hook]);

        $mapper->denormalize(
            ['id' => 7, 'name' => 'John', 'address' => ['city' => 'Paris'], 'code' => 5],
            UserStub::class,
        );

        self::assertSame(['beforeDenormalize', 'afterDenormalize'], $hook->calls);
    }

    public function testExceptionOfHookAfterDenormalizationIsViolationUnseenByCheck(): void
    {
        $hook = new HookStub(AddressStub::class, afterDenormalize: static function (): void {
            throw new \InvalidArgumentException('City is not served');
        });

        $mapper = self::createMapper(hooks: [$hook]);
        $value = ['id' => 7, 'name' => 'John', 'address' => ['city' => 'Paris'], 'code' => 5];

        self::assertTrue($mapper->isDenormalizable($value, UserStub::class));

        $error = self::captureThrowable(static fn(): mixed => $mapper->denormalize($value, UserStub::class));

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame('$.address: Value array(1) is rejected', $error->getMessage());
        self::assertInstanceOf(\InvalidArgumentException::class, $error->violations[0]->cause);
    }

    public function testErrorOfProgramPassesThroughCompositeAsIs(): void
    {
        $mapper = self::createMapper();

        $error = self::captureThrowable(
            static fn(): mixed => $mapper->denormalize([['value' => 'x']], 'list<' . MismatchedTypeStub::class . '>'),
        );

        self::assertInstanceOf(\TypeError::class, $error);
    }

    public function testNormalizationRejectsObjectOfOtherClass(): void
    {
        self::assertNormalizationFails(
            UserStub::class,
            self::createAddress('Paris'),
            '$: Value must be of type TypeLang\Mapper\Tests\Stub\UserStub, but object(TypeLang\Mapper\Tests\Stub\AddressStub) given',
        );
    }

    private static function createAddress(string $city): AddressStub
    {
        $address = new AddressStub();
        $address->city = $city;

        return $address;
    }

    /**
     * @param non-empty-string $name
     */
    private static function createUser(int $id, string $name, ?AddressStub $address, int|string $code): UserStub
    {
        $user = new UserStub();
        $user->id = $id;
        $user->name = $name;
        $user->address = $address;
        $user->code = $code;

        return $user;
    }


    public function testWritesReadonlyPropertyDeclaredByParent(): void
    {
        $child = self::createMapper()->denormalize(['id' => 1, 'name' => 'a'], ReadonlyChildStub::class);

        self::assertInstanceOf(ReadonlyChildStub::class, $child);
        self::assertSame(1, $child->id);
    }

    public function testErrorOfHookIsViolationOfObject(): void
    {
        $hooks = [
            new HookStub(AddressStub::class, beforeDenormalize: static function (): never {
                throw new \DomainException('a');
            }),
            new HookStub(UserStub::class, afterDenormalize: static function (): never {
                throw new \DomainException('b');
            }),
        ];
        $mapper = self::createMapper(hooks: $hooks);

        self::assertFalse($mapper->isDenormalizable(['city' => 'Paris'], AddressStub::class));

        $error = self::captureThrowable(static fn(): mixed => $mapper->denormalize(['city' => 'Paris'], AddressStub::class));

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame('$: Value array(1) is rejected', $error->getMessage());
        self::assertInstanceOf(\DomainException::class, $error->getPrevious());

        $error = self::captureThrowable(static fn(): mixed => $mapper->denormalize(
            ['id' => 7, 'name' => 'John', 'address' => null, 'code' => 5],
            UserStub::class,
        ));

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame('$: Value array(4) is rejected', $error->getMessage());
        self::assertInstanceOf(\DomainException::class, $error->getPrevious());
    }

    public function testMessageOfPropertyReplacesMessagesOfItsValueOnly(): void
    {
        $mapper = self::createMapper(config: new Configuration(maxViolations: 10));
        $type = MessageOwnerStub::class;

        $error = self::captureThrowable(static fn(): mixed => $mapper->denormalize(['person' => ['age' => 'x']], $type));

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame(
            '$.person.age: Age must be a number, "x" given (and 1 more)',
            $error->getMessage(),
        );

        $error = self::captureThrowable(static fn(): mixed => $mapper->denormalize(['person' => 5], $type));

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame('$.person: Person is invalid', $error->getMessage());
    }
}
