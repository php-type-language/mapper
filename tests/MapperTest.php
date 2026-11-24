<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests;

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Diagnostics\Printer\Path\JsonPathPrinter;
use TypeLang\Mapper\Diagnostics\ViolationCode;
use TypeLang\Mapper\Diagnostics\ViolationFormatter;
use TypeLang\Mapper\Exception\InvalidValueException;
use TypeLang\Mapper\Exception\MapperExceptionInterface;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Tests\Stub\AddressStub;
use TypeLang\Mapper\Tests\Stub\AliasedStub;
use TypeLang\Mapper\Tests\Stub\BranchNodeStub;
use TypeLang\Mapper\Tests\Stub\ChainLinkStub;
use TypeLang\Mapper\Tests\Stub\DecimalCommaCoercerStub;
use TypeLang\Mapper\Tests\Stub\DuplicateNameStub;
use TypeLang\Mapper\Tests\Stub\JsonArrayCoercerStub;
use TypeLang\Mapper\Tests\Stub\NativeUnionStub;
use TypeLang\Mapper\Tests\Stub\Shape\CircleStub;
use TypeLang\Mapper\Tests\Stub\Shape\ShapeStub;
use TypeLang\Mapper\Tests\Stub\StatusStub;
use TypeLang\Mapper\Tests\Stub\UserStub;
use TypeLang\Parser\Exception\ParserExceptionInterface;

final class MapperTest extends TestCase
{
    public function testDenormalizesByTypeString(): void
    {
        self::assertSame(42, (new Mapper())->denormalize('42', 'int'));
    }

    public function testWrongMetadataIsErrorOfDeclarationInEveryCall(): void
    {
        $mapper = new Mapper();
        $calls = [
            static fn(): mixed => $mapper->denormalize(['id' => 1], DuplicateNameStub::class),
            static fn(): mixed => $mapper->normalize([new DuplicateNameStub()]),
            static fn(): bool => $mapper->isNormalizable([new DuplicateNameStub()]),
        ];

        foreach ($calls as $call) {
            $error = self::captureThrowable($call);

            self::assertInstanceOf(TypeDefinitionException::class, $error);
            self::assertInstanceOf(InvalidMetadataException::class, $error->getPrevious());
            self::assertStringStartsWith(
                \sprintf(
                    'Type "%1$s" cannot be built, the mapping for class "%1$s" contains errors:',
                    DuplicateNameStub::class,
                ),
                $error->getMessage(),
            );
        }
    }

    public function testTypeWrittenWrongIsErrorOfDeclaration(): void
    {
        $error = self::captureThrowable(static fn(): mixed => (new Mapper())->denormalize([], 'list<int'));

        self::assertInstanceOf(TypeDefinitionException::class, $error);
        self::assertInstanceOf(ParserExceptionInterface::class, $error->getPrevious());
        self::assertStringStartsWith('Type "list<int" is written wrong: ', $error->getMessage());
    }

    public function testEveryErrorOfConversionIsErrorOfMapper(): void
    {
        $mapper = new Mapper();

        self::assertInstanceOf(
            MapperExceptionInterface::class,
            self::captureThrowable(static fn(): mixed => $mapper->denormalize('x', 'int')),
        );
        self::assertInstanceOf(
            MapperExceptionInterface::class,
            self::captureThrowable(static fn(): mixed => $mapper->denormalize('x', 'unknown-type')),
        );
    }

    public function testCoercersOfUserReplaceDefaultOnes(): void
    {
        $mapper = new Mapper(coercers: [
            'float' => new DecimalCommaCoercerStub(),
            'array' => new JsonArrayCoercerStub(),
        ]);

        self::assertSame(1.5, $mapper->denormalize('1,5', 'float'));
        self::assertSame([1, 2], $mapper->denormalize('[1, 2]', 'list<int>'));

        $address = $mapper->denormalize('{"city": "Paris"}', AddressStub::class);

        self::assertInstanceOf(AddressStub::class, $address);
        self::assertSame('Paris', $address->city);
    }

    public function testStrictModeSkipsCoercersOfScalars(): void
    {
        $mapper = new Mapper(
            config: new Configuration(strict: true),
            coercers: ['float' => new DecimalCommaCoercerStub()],
        );

        $this->expectException(InvalidValueException::class);

        $mapper->denormalize('1,5', 'float');
    }

    public function testDenormalizationAndNormalizationAreInverse(): void
    {
        $mapper = new Mapper();
        $data = ['id' => 7, 'name' => 'John', 'role' => 'admin', 'address' => ['city' => 'Paris'], 'code' => 'A1'];

        $user = $mapper->denormalize($data, UserStub::class);

        self::assertInstanceOf(UserStub::class, $user);
        self::assertSame($data, $mapper->normalize($user, UserStub::class));
    }

    public function testInfersTypeOfNormalizedValue(): void
    {
        $mapper = new Mapper();
        $address = new AddressStub();
        $address->city = 'Paris';

        self::assertSame(['city' => 'Paris'], $mapper->normalize($address));
        self::assertSame('active', $mapper->normalize(StatusStub::Active));
        self::assertSame(42, $mapper->normalize(42));
        self::assertTrue($mapper->isNormalizable($address));
    }

    public function testInfersTypeOfEachNestedValue(): void
    {
        $mapper = new Mapper();
        $address = new AddressStub();
        $address->city = 'Paris';
        $object = new \stdClass();
        $object->address = $address;

        self::assertSame(['address' => ['city' => 'Paris']], $mapper->normalize($object));
        self::assertSame([['city' => 'Paris']], $mapper->normalize([$address]));
    }

    public function testInfersTypeOfObjectOfAnonymousClass(): void
    {
        $object = new class {
            public int $id = 7;
        };

        self::assertSame(['id' => 7], (new Mapper())->normalize($object));
    }

    public function testChecksValueByPredicates(): void
    {
        $mapper = new Mapper();

        self::assertTrue($mapper->isDenormalizable('42', 'int'));
        self::assertFalse($mapper->isDenormalizable('x', 'int'));
        self::assertFalse($mapper->isNormalizable(new AddressStub(), UserStub::class));
    }

    public function testStrictConfigurationDisablesCoercion(): void
    {
        $error = self::captureThrowable(
            static fn(): mixed => (new Mapper(new Configuration(strict: true)))->denormalize('42', 'int'),
        );

        self::assertInstanceOf(InvalidValueException::class, $error);
    }

    public function testFailsOnFirstViolationByDefault(): void
    {
        $error = self::captureThrowable(
            static fn(): mixed => (new Mapper())->denormalize(['a', 2, 'c'], 'list<int>'),
        );

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame(['$[0]'], self::printPaths($error));
    }

    public function testCollectsViolationsOfEveryLevelInOrderOfData(): void
    {
        $mapper = new Mapper(new Configuration(maxViolations: 10));
        $data = [
            ['id' => 'x', 'address' => ['city' => []], 'code' => 1],
            ['id' => 1, 'name' => 'John', 'address' => null, 'code' => 1],
            ['id' => 2, 'name' => '', 'address' => null, 'code' => null],
        ];

        $error = self::captureThrowable(
            static fn(): mixed => $mapper->denormalize($data, \sprintf('list<%s>', UserStub::class)),
        );

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame(
            ['$[0].id', '$[0].name', '$[0].address.city', '$[2].name', '$[2].code'],
            self::printPaths($error),
        );
        self::assertStringEndsWith('(and 4 more)', $error->getMessage());
    }

    public function testCollectsViolationsOfKeysAndFieldsOfShapes(): void
    {
        $mapper = new Mapper(new Configuration(maxViolations: 10));

        $error = self::captureThrowable(static fn(): mixed => $mapper->denormalize(
            ['a' => ['x' => 'no'], 'b' => ['x' => 1, 'y' => 2]],
            'array<string, array{x: int, y: int}>',
        ));

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame(['$.a.x', '$.a.y'], self::printPaths($error));

        $error = self::captureThrowable(
            static fn(): mixed => $mapper->denormalize(['a' => 1, 5 => 'x', 'b' => 2], 'array<int, int>'),
        );

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame(['$.a', '$[5]', '$.b'], self::printPaths($error));
    }

    public function testStopsCollectingAtLimitOfViolations(): void
    {
        $mapper = new Mapper(new Configuration(maxViolations: 3));

        $error = self::captureThrowable(
            static fn(): mixed => $mapper->denormalize([['a', 'b'], ['c', 'd'], ['e']], 'list<list<int>>'),
        );

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame(['$[0][0]', '$[0][1]', '$[1][0]'], self::printPaths($error));
    }

    public function testAliasIsNotExtraField(): void
    {
        $mapper = new Mapper(new Configuration(rejectExtraFields: true));

        $object = $mapper->denormalize(['mail' => 'a@b.c', 'login' => 'John'], AliasedStub::class);

        self::assertInstanceOf(AliasedStub::class, $object);
        self::assertSame('John', $object->name);
    }
    public function testNativeUnionWithNullAndFalseIsMapped(): void
    {
        $mapper = new Mapper();

        $object = $mapper->denormalize(['value' => null, 'limit' => false], NativeUnionStub::class);

        self::assertInstanceOf(NativeUnionStub::class, $object);
        self::assertNull($object->value);
        self::assertFalse($object->limit);
        self::assertSame(1.5, $mapper->denormalize(['value' => 1.5], NativeUnionStub::class)->value);
        self::assertSame(['value' => null, 'limit' => 10], $mapper->normalize(
            $mapper->denormalize(['limit' => 10], NativeUnionStub::class),
        ));
    }

    public function testDropsExtraFieldsByDefault(): void
    {
        $mapper = new Mapper();

        self::assertTrue($mapper->isDenormalizable(['city' => 'Paris', 'zip' => 1], AddressStub::class));
        self::assertSame(['a' => 1], $mapper->denormalize(['a' => 1, 'b' => 2], 'array{a: int}'));
    }

    public function testRejectsExtraFieldsOfClassesAndSealedShapes(): void
    {
        $mapper = new Mapper(new Configuration(maxViolations: 10, rejectExtraFields: true));
        $data = ['city' => 'Paris', 'zip' => 1, '' => 2, "a\nb" => 3];

        self::assertFalse($mapper->isDenormalizable($data, AddressStub::class));

        $error = self::captureThrowable(static fn(): mixed => $mapper->denormalize($data, AddressStub::class));

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame(['$.zip', "$['']", "$['a\\nb']"], self::printPaths($error));
        self::assertStringStartsWith('$.zip: Unexpected field "zip"', $error->getMessage());
        self::assertSame('Unexpected field "a\nb"', self::createFormatter()->format($error->violations[2]));

        self::assertFalse($mapper->isDenormalizable(['a' => 1, 'b' => 2], 'array{a: int}'));
        self::assertTrue($mapper->isDenormalizable(['a' => 1, 'b' => 2], 'array{a: int, ...}'));

        $error = self::captureThrowable(
            static fn(): mixed => $mapper->denormalize(['a' => 1, 'b' => 2], 'array{a: int}'),
        );

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame(['$.b'], self::printPaths($error));
    }

    public function testTagOfBaseIsNotExtraFieldOfItsClass(): void
    {
        $mapper = new Mapper(new Configuration(rejectExtraFields: true));
        $data = ['type' => 'circle', 'radius' => 1];

        self::assertInstanceOf(CircleStub::class, $mapper->denormalize($data, ShapeStub::class));
        self::assertInstanceOf(CircleStub::class, $mapper->denormalize($data, CircleStub::class));
        self::assertFalse($mapper->isDenormalizable($data + ['side' => 2], ShapeStub::class));
    }

    public function testChainOfObjectsAtLimitOfDepthIsNormalized(): void
    {
        $mapper = new Mapper(new Configuration(maxDepth: 5));
        $chain = ChainLinkStub::chain(5);

        self::assertTrue($mapper->isNormalizable($chain, ChainLinkStub::class));
        self::assertSame(
            ['next' => ['next' => ['next' => ['next' => ['next' => null, 'children' => []],
                'children' => []], 'children' => []], 'children' => []], 'children' => []],
            $mapper->normalize($chain, ChainLinkStub::class),
        );
    }

    public function testChainOfObjectsBeyondLimitOfDepthIsRejectedWithPath(): void
    {
        $mapper = new Mapper(new Configuration(maxDepth: 5));
        $chain = ChainLinkStub::chain(6);

        $error = self::captureThrowable(static fn(): mixed => $mapper->normalize($chain, ChainLinkStub::class));

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame(['$.next.next.next.next.next'], self::printPaths($error));
        self::assertSame(
            '$.next.next.next.next.next: Nesting depth exceeds the limit of 5',
            $error->getMessage(),
        );
    }

    public function testCyclicGraphIsRejectedByDefaultLimitOfDepth(): void
    {
        $mapper = new Mapper();
        $node = new ChainLinkStub();
        $node->next = $node;

        $error = self::captureThrowable(static fn(): mixed => $mapper->normalize($node));

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame(ViolationCode::DepthExceeded, $error->violations[0]->code);
        self::assertCount(512, $error->violations[0]->path->segments);
    }

    public function testCyclicObjectBehindUnionIsRejected(): void
    {
        $mapper = new Mapper(new Configuration(maxDepth: 3));
        $node = new ChainLinkStub();
        $node->next = $node;

        $error = self::captureThrowable(
            static fn(): mixed => $mapper->normalize($node, ChainLinkStub::class . '|int'),
        );

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame(ViolationCode::InvalidType, $error->violations[0]->code);
    }

    public function testBranchingCyclicGraphIsRejectedByDefaultLimitOfDepthQuickly(): void
    {
        $mapper = new Mapper();
        $root = BranchNodeStub::create();

        $error = self::captureThrowable(static fn(): mixed => $mapper->normalize($root));

        self::assertFalse($mapper->isNormalizable($root));
        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame(ViolationCode::DepthExceeded, $error->violations[0]->code);
    }

    public function testCycleThroughListIsRejectedWithPath(): void
    {
        $mapper = new Mapper(new Configuration(maxDepth: 3));
        $node = new ChainLinkStub();
        $node->children = [$node];

        $error = self::captureThrowable(static fn(): mixed => $mapper->normalize($node, ChainLinkStub::class));

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame(['$.children[0].children[0].children[0]'], self::printPaths($error));
    }

    public function testCyclicDynamicObjectAndArrayAreRejected(): void
    {
        $mapper = new Mapper(new Configuration(maxDepth: 4));
        $object = new \stdClass();
        $object->self = $object;
        $array = [];
        $array['self'] = &$array;

        foreach ([$object, $array] as $value) {
            $error = self::captureThrowable(static fn(): mixed => $mapper->normalize($value));

            self::assertInstanceOf(InvalidValueException::class, $error);
            self::assertSame(ViolationCode::DepthExceeded, $error->violations[0]->code);
        }
    }

    public function testMapperNormalizesAgainAfterRejectedDepth(): void
    {
        $mapper = new Mapper(new Configuration(maxDepth: 3));
        $node = new ChainLinkStub();
        $node->next = $node;

        for ($i = 0; $i < 3; ++$i) {
            self::captureThrowable(static fn(): mixed => $mapper->normalize($node));
        }

        self::assertSame(
            ['next' => null, 'children' => []],
            $mapper->normalize(ChainLinkStub::chain(1)),
        );
    }

    public function testDepthIsNotLimitedOnDenormalization(): void
    {
        $mapper = new Mapper(new Configuration(maxDepth: 1));
        $data = ['next' => ['next' => ['next' => null]]];

        self::assertInstanceOf(ChainLinkStub::class, $mapper->denormalize($data, ChainLinkStub::class));
    }

    /**
     * @return list<string>
     */
    private static function printPaths(InvalidValueException $error): array
    {
        $printer = new JsonPathPrinter();
        $paths = [];

        foreach ($error->violations as $violation) {
            $paths[] = $printer->print($violation->path);
        }

        return $paths;
    }
}
