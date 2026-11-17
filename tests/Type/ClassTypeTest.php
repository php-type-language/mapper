<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhp;
use TypeLang\Mapper\Context\ClassAccessors;
use TypeLang\Mapper\Context\Configuration;
use TypeLang\Mapper\Context\ClassTypes;
use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\InvalidValueException;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Instantiator\Exception\ClassNotInstantiableException;
use TypeLang\Mapper\Instantiator\InstantiatorFactory;
use TypeLang\Mapper\Instantiator\InstantiatorInterface;
use TypeLang\Mapper\Instantiator\Strategy\InstantiationStrategyInterface;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\Provider\MetadataProviderInterface;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyNotWritableException;
use TypeLang\Mapper\PropertyAccessor\PropertyAccessorFactory;
use TypeLang\Mapper\PropertyAccessor\PropertyReaderInterface;
use TypeLang\Mapper\PropertyAccessor\PropertyWriterInterface;
use TypeLang\Mapper\PropertyAccessor\Strategy\AccessStrategyInterface;
use TypeLang\Mapper\Tests\Stub\AbstractShapeStub;
use TypeLang\Mapper\Tests\Stub\AddressStub;
use TypeLang\Mapper\Tests\Stub\ComputedStub;
use TypeLang\Mapper\Tests\Stub\HookStub;
use TypeLang\Mapper\Tests\Stub\MessageOwnerStub;
use TypeLang\Mapper\Tests\Stub\MessageStub;
use TypeLang\Mapper\Tests\Stub\MismatchedTypeStub;
use TypeLang\Mapper\Tests\Stub\NodeStub;
use TypeLang\Mapper\Tests\Stub\ObjectFormStub;
use TypeLang\Mapper\Tests\Stub\ReadonlyChildStub;
use TypeLang\Mapper\Tests\Stub\ReadonlyStub;
use TypeLang\Mapper\Tests\Stub\RelativeNameStub;
use TypeLang\Mapper\Tests\Stub\RenamedStub;
use TypeLang\Mapper\Tests\Stub\SkipStub;
use TypeLang\Mapper\Tests\Stub\StdClassChildStub;
use TypeLang\Mapper\Tests\Stub\StrictnessStub;
use TypeLang\Mapper\Tests\Stub\UntypedStub;
use TypeLang\Mapper\Tests\Stub\UserStub;
use TypeLang\Mapper\Tests\TestCase;
use TypeLang\Mapper\Type\TypeInterface;
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
            self::createRepository(isStrict: true),
        );
    }

    public function testRejectsSubclassOfStdClass(): void
    {
        $input = new StdClassChildStub();
        $input->city = 'Paris';

        self::assertDenormalizationFails(
            AddressStub::class,
            $input,
            \sprintf(
                '$: Value must be of type "%s", but object(%s) given',
                AddressStub::class,
                StdClassChildStub::class,
            ),
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
        $error = self::captureThrowable(static fn(): mixed => self::getType($class));

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
        $error = self::captureThrowable(static fn(): mixed => self::getType($class));

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

        $repository = self::createRepository(
            accessors: new ClassAccessors(instantiators: new InstantiatorFactory([$strategy])),
        );

        $shape = self::getType(AbstractShapeStub::class, $repository)
            ->denormalize(['name' => 'circle'], self::getContext($repository));

        self::assertInstanceOf(AbstractShapeStub::class, $shape);
        self::assertSame('circle', $shape->name);
    }

    public function testStrategyRejectingClassOfRestoredTypeIsErrorOfType(): void
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
        $instantiators = new InstantiatorFactory([$strategy]);

        $type = \unserialize(\serialize(self::getType(UserStub::class)));
        $context = new Context(
            types: new ClassTypes(self::createRepository()),
            accessors: new ClassAccessors(instantiators: $instantiators),
        );

        self::assertInstanceOf(TypeInterface::class, $type);

        $error = self::captureThrowable(static fn(): mixed => $type->denormalize(
            ['id' => 7, 'name' => 'John', 'address' => ['city' => 'Paris'], 'code' => 5],
            $context,
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
        $user = self::getType(UserStub::class)->denormalize(
            ['id' => 7, 'name' => 'John', 'address' => null, 'code' => 5],
            self::getContext(self::createRepository()),
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
            '$.address.city: Value must be of type "string", but array(0) given',
        );
    }

    public function testRejectsValueThatIsNotArrayOrObject(): void
    {
        self::assertDenormalizationFails(
            UserStub::class,
            'x',
            \sprintf('$: Value must be of type "%s", but "x" given', UserStub::class),
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
            \sprintf('$.next.next: Value must be of type "%s", but "x" given', NodeStub::class),
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
        $repository = self::createRepository();

        foreach ([['name' => 'John'], ['name' => 'John', 'greeting' => 'Hi']] as $input) {
            $object = self::getType(ComputedStub::class, $repository)
                ->denormalize($input, self::getContext($repository));

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

        $repository = self::createRepository(metadata: $metadata);

        $error = self::captureThrowable(static fn(): mixed => self::getType(ComputedStub::class, $repository));

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
        $repository = self::createRepository();

        $error = self::captureThrowable(
            static fn(): mixed => self::getType(UserStub::class, $repository)
                ->normalize($user, self::getContext($repository)),
        );

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
            self::createRepository(isStrict: true),
        );
        self::assertDenormalizationFails(
            StrictnessStub::class,
            ['strict' => '1', 'loose' => 2],
            '$.strict: Value must be of type "int", but "1" given',
        );
    }

    public function testPropertyWithoutTypeIsErrorOfType(): void
    {
        $error = self::captureThrowable(static fn(): mixed => self::getType(UntypedStub::class));

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
            '$.code: Value must be of type "int|string", but 1.5 given',
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
            \sprintf('$: Person is expected, object(%s) given', AddressStub::class),
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
        $repository = self::createRepository();
        $type = self::getType(ObjectFormStub::class, $repository);

        self::assertTrue($type->isNormalizable(new ObjectFormStub(), self::getContext($repository)));
        self::assertEquals(
            (object) ['theme' => 'dark'],
            $type->normalize(new ObjectFormStub(), self::getContext($repository)),
        );
    }

    public function testConfigurationNormalizesObjectsAsStdClass(): void
    {
        $repository = self::createRepository(config: new Configuration(isObjectAsArray: false));

        $type = self::getType(AddressStub::class, $repository);
        $address = self::createAddress('Paris');

        self::assertEquals((object) ['city' => 'Paris'], $type->normalize($address, self::getContext($repository)));
        self::assertEquals(
            (object) ['city' => 'Paris'],
            $type->normalize($address, self::getContext($repository)->withStrictTypes(true)),
        );
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

        $repository = self::createRepository(
            accessors: new ClassAccessors(properties: new PropertyAccessorFactory([$strategy])),
        );

        $object = self::getType(RenamedStub::class, $repository)
            ->denormalize(['e-mail' => 'a@b.c', 'name' => 'John'], self::getContext($repository));

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

        $repository = self::createRepository(
            accessors: new ClassAccessors(instantiators: new InstantiatorFactory([$strategy])),
        );

        $address = self::getType(AddressStub::class, $repository)
            ->denormalize(['id' => 1, 'city' => 'Paris'], self::getContext($repository));

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
            self::createRepository(hooks: [$hook]),
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

        $repository = self::createRepository(hooks: [$hook]);
        $type = self::getType(AddressStub::class, $repository);

        self::assertTrue($type->isDenormalizable(['city' => 'Paris'], self::getContext($repository)));
        self::assertSame(['beforeDenormalize'], $hook->calls);

        $address = $type->denormalize(['city' => 'Paris'], self::getContext($repository));

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
            self::createRepository(hooks: [$hook]),
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
            self::createRepository(hooks: [$hook]),
        );
    }

    public function testHookRunsForObjectsOfItsClassOnly(): void
    {
        $hook = new HookStub(AddressStub::class);
        $repository = self::createRepository(hooks: [$hook]);

        self::getType(UserStub::class, $repository)->denormalize(
            ['id' => 7, 'name' => 'John', 'address' => ['city' => 'Paris'], 'code' => 5],
            self::getContext($repository),
        );

        self::assertSame(['beforeDenormalize', 'afterDenormalize'], $hook->calls);
    }

    public function testExceptionOfHookAfterDenormalizationIsViolationUnseenByCheck(): void
    {
        $hook = new HookStub(AddressStub::class, afterDenormalize: static function (): void {
            throw new \InvalidArgumentException('City is not served');
        });

        $repository = self::createRepository(hooks: [$hook]);
        $type = self::getType(UserStub::class, $repository);
        $value = ['id' => 7, 'name' => 'John', 'address' => ['city' => 'Paris'], 'code' => 5];

        self::assertTrue($type->isDenormalizable($value, self::getContext($repository)));

        $error = self::captureThrowable(
            static fn(): mixed => $type->denormalize($value, self::getContext($repository)),
        );

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame('$.address: City is not served', $error->getMessage());
    }

    public function testErrorOfProgramPassesThroughCompositeAsIs(): void
    {
        $repository = self::createRepository();
        $type = self::getType('list<' . MismatchedTypeStub::class . '>', $repository);

        $error = self::captureThrowable(
            static fn(): mixed => $type->denormalize([['value' => 'x']], self::getContext($repository)),
        );

        self::assertInstanceOf(\TypeError::class, $error);
    }

    public function testNormalizationRejectsObjectOfOtherClass(): void
    {
        self::assertNormalizationFails(
            UserStub::class,
            self::createAddress('Paris'),
            \sprintf('$: Value must be of type "%s", but object(%s) given', UserStub::class, AddressStub::class),
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
        $child = self::getType(ReadonlyChildStub::class)
            ->denormalize(['id' => 1, 'name' => 'a'], self::getContext(self::createRepository()));

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
        $repository = self::createRepository(hooks: $hooks);
        $context = self::getContext($repository);
        $address = self::getType(AddressStub::class, $repository);

        self::assertFalse($address->isDenormalizable(['city' => 'Paris'], $context));

        $error = self::captureThrowable(static fn(): mixed => $address->denormalize(['city' => 'Paris'], $context));

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame('$: a', $error->getMessage());
        self::assertInstanceOf(\DomainException::class, $error->getPrevious());

        $error = self::captureThrowable(static fn(): mixed => self::getType(UserStub::class, $repository)->denormalize(
            ['id' => 7, 'name' => 'John', 'address' => null, 'code' => 5],
            $context,
        ));

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame('$: b', $error->getMessage());
    }

    public function testMessageOfPropertyReplacesMessagesOfItsValueOnly(): void
    {
        $repository = self::createRepository(config: new Configuration(maxViolations: 10));
        $context = self::getContext($repository);
        $type = self::getType(MessageOwnerStub::class, $repository);

        $error = self::captureThrowable(static fn(): mixed => $type->denormalize(['person' => ['age' => 'x']], $context));

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame(
            '$.person.age: Age must be a number, "x" given (and 1 more)',
            $error->getMessage(),
        );

        $error = self::captureThrowable(static fn(): mixed => $type->denormalize(['person' => 5], $context));

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame('$.person: Person is invalid', $error->getMessage());
    }
}
