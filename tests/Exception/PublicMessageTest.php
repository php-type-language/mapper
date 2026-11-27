<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Exception;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Diagnostics\Printer\Path\JsonPathPrinter;
use TypeLang\Mapper\Diagnostics\PublicViolationFormatter;
use TypeLang\Mapper\Diagnostics\ViolationFormatter;
use TypeLang\Mapper\Exception\InvalidValueException;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Tests\Stub\AddressDecoratorStub;
use TypeLang\Mapper\Tests\Stub\AddressStub;
use TypeLang\Mapper\Tests\Stub\ClassExpanderStub;
use TypeLang\Mapper\Tests\Stub\ConstantsStub;
use TypeLang\Mapper\Tests\Stub\FailingExpanderStub;
use TypeLang\Mapper\Tests\Stub\HookStub;
use TypeLang\Mapper\Tests\Stub\LevelStub;
use TypeLang\Mapper\Tests\Stub\NodeStub;
use TypeLang\Mapper\Tests\Stub\OpaqueTypeExpanderStub;
use TypeLang\Mapper\Tests\Stub\OrderStub;
use TypeLang\Mapper\Tests\Stub\RenamedFieldStub;
use TypeLang\Mapper\Tests\Stub\StatusStub;
use TypeLang\Mapper\Tests\Stub\SuitStub;
use TypeLang\Mapper\Tests\Stub\UserStub;
use TypeLang\Mapper\Tests\TestCase;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\WrappingTypeNode\NullableTypeNode;

final class PublicMessageTest extends TestCase
{
    /**
     * @return iterable<non-empty-string, array{non-empty-string, mixed, non-empty-string}>
     */
    public static function publicMessageDataProvider(): iterable
    {
        yield 'backed enum' => [
            StatusStub::class,
            'x',
            '$: Value must be of type \'active\'|\'banned\', but "x" given',
        ];
        yield 'unit enum' => [
            SuitStub::class,
            42,
            '$: Value must be of type \'Hearts\'|\'Spades\', but 42 given',
        ];
        yield 'class' => [
            AddressStub::class,
            'x',
            '$: Value must be of type object{city: string}, but "x" given',
        ];
        yield 'union of classes' => [
            UserStub::class . '|' . AddressStub::class,
            'x',
            '$: Value must be of type object{id: int, name: non-empty-string, role?: string, address: ?object, '
                . 'code: int|string}|object{city: string}, but "x" given',
        ];
        yield 'element of list of classes' => [
            'list<' . AddressStub::class . '>',
            ['x'],
            '$[0]: Value must be of type object{city: string}, but "x" given',
        ];
        yield 'enum in list of property' => [
            OrderStub::class,
            ['statuses' => ['x']],
            '$.statuses[0]: Value must be of type \'active\'|\'banned\', but "x" given',
        ];
        yield 'enum in shape of property' => [
            OrderStub::class,
            ['items' => [['level' => 5, 'suit' => 'Hearts']]],
            '$.items[0].level: Value must be of type 1|2, but 5 given',
        ];
        yield 'nullable class of property' => [
            OrderStub::class,
            ['address' => 'x'],
            '$.address: Value must be of type ?object{city: string}, but "x" given',
        ];
        yield 'date of property' => [
            OrderStub::class,
            ['createdAt' => []],
            '$.createdAt: Value must be of type ?string, but array(0) given',
        ];
        yield 'mask of constants' => [
            ConstantsStub::class . '::CODE_*',
            500,
            '$: Value must be of type 200|201, but 500 given',
        ];
        yield 'values of enum' => [
            'value-of<' . LevelStub::class . '>',
            5,
            '$: Value must be of type 1|2, but 5 given',
        ];
        yield 'type without classes' => [
            'list<non-empty-string>',
            [''],
            '$[0]: Value must be of type non-empty-string (non-empty)',
        ];
    }

    /**
     * @param non-empty-string $type
     * @param non-empty-string $expected
     */
    #[DataProvider('publicMessageDataProvider')]
    public function testPublicMessageHidesNamesOfApplication(string $type, mixed $input, string $expected): void
    {
        self::assertSame($expected, self::formatFirstViolation(self::createMapper(), $type, $input));
    }

    public function testMessageOfErrorIsPublic(): void
    {
        $error = self::captureThrowable(
            static fn(): mixed => self::createMapper()->denormalize(['statuses' => ['x']], OrderStub::class),
        );

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame(
            '$.statuses[0]: Value must be of type \'active\'|\'banned\', but "x" given',
            $error->getMessage(),
        );
    }

    public function testDeveloperFormatterKeepsNamesOfApplication(): void
    {
        $error = self::captureThrowable(
            static fn(): mixed => self::createMapper()->denormalize(['statuses' => ['x']], OrderStub::class),
        );

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame(
            \sprintf('Value must be of type \\%s, but "x" given', StatusStub::class),
            self::createFormatter(shouldPrintDeclaredType: true)->format($error->violations[0]),
        );
    }

    public function testMapperGivesViolationsPublicTypesKeepingDeclaredOnes(): void
    {
        $error = self::captureThrowable(
            static fn(): mixed => self::createMapper()->denormalize(['address' => 'x'], OrderStub::class),
        );

        self::assertInstanceOf(InvalidValueException::class, $error);

        $violation = $error->violations[0];

        self::assertInstanceOf(NullableTypeNode::class, $violation->declared);
        self::assertInstanceOf(NamedTypeNode::class, $violation->declared->type);
        self::assertSame(AddressStub::class, \ltrim($violation->declared->type->name->toString(), '\\'));
        self::assertInstanceOf(NullableTypeNode::class, $violation->expected);
        self::assertInstanceOf(NamedTypeNode::class, $violation->expected->type);
        self::assertSame('object', $violation->expected->type->name->toString());
    }

    public function testNormalizationErrorIsRenderedForDeveloperWithDeclaredType(): void
    {
        $error = self::captureThrowable(
            static fn(): mixed => self::createMapper()->normalize('active', StatusStub::class),
        );

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertSame(
            \sprintf('$: Value must be of type %s, but "active" given', StatusStub::class),
            $error->getMessage(),
        );
        self::assertNull($error->violations[0]->expected);
    }

    public function testErrorThrownWithoutMapperIsRenderedWithoutType(): void
    {
        $error = InvalidValueException::becauseInvalidType('x', new NamedTypeNode(Name::from(AddressStub::class)));

        self::assertNull($error->violations[0]->expected);
        self::assertSame('Value "x" is invalid', self::createPublicFormatter()->format($error->violations[0]));
    }

    /**
     * @return iterable<non-empty-string, array{int<0, max>, class-string, non-empty-string}>
     */
    public static function depthDataProvider(): iterable
    {
        yield 'class without fields by default' => [0, AddressStub::class, 'object'];
        yield 'fields of class' => [1, AddressStub::class, 'object{city: string}'];
        yield 'nested class without fields' => [
            1,
            OrderStub::class,
            'object{statuses?: list<\'active\'|\'banned\'>, items?: list<array{level: 1|2, suit: \'Hearts\'|'
                . '\'Spades\'}>, address?: ?object, createdAt?: ?string}',
        ];
        yield 'fields of nested class' => [
            2,
            OrderStub::class,
            'object{statuses?: list<\'active\'|\'banned\'>, items?: list<array{level: 1|2, suit: \'Hearts\'|'
                . '\'Spades\'}>, address?: ?object{city: string}, createdAt?: ?string}',
        ];
        yield 'recursive class to depth' => [
            3,
            NodeStub::class,
            'object{name: string, next: ?object{name: string, next: ?object{name: string, next: ?object}}}',
        ];
    }

    /**
     * @param int<0, max> $depth
     * @param class-string $class
     * @param non-empty-string $expected
     */
    #[DataProvider('depthDataProvider')]
    public function testDepthLimitsDescribedFieldsOfClasses(int $depth, string $class, string $expected): void
    {
        $mapper = self::createMapper(config: new Configuration(expectedTypeDepth: $depth));

        self::assertSame(
            \sprintf('$: Value must be of type %s, but "x" given', $expected),
            self::formatFirstViolation($mapper, $class, 'x'),
        );
    }

    /**
     * @return iterable<non-empty-string, array{int<1, max>, non-empty-string}>
     */
    public static function maxFieldsDataProvider(): iterable
    {
        yield 'fields over limit' => [1, 'object{name: string, ...}'];
        yield 'fields at limit' => [2, 'object{name: string, next: ?object}'];
        yield 'fields under limit' => [3, 'object{name: string, next: ?object}'];
    }

    /**
     * @param int<1, max> $maxFields
     * @param non-empty-string $expected
     */
    #[DataProvider('maxFieldsDataProvider')]
    public function testMaxFieldsHidesOtherFieldsOfClass(int $maxFields, string $expected): void
    {
        $mapper = self::createMapper(config: new Configuration(expectedTypeDepth: 1, expectedTypeMaxFields: $maxFields));

        self::assertSame(
            \sprintf('$: Value must be of type %s, but "x" given', $expected),
            self::formatFirstViolation($mapper, NodeStub::class, 'x'),
        );
    }

    public function testHiddenFieldWithoutPublicFormKeepsFieldsOfClass(): void
    {
        $mapper = self::createMapper(
            decorators: [new AddressDecoratorStub()],
            config: new Configuration(expectedTypeDepth: 1, expectedTypeMaxFields: 2),
        );

        self::assertSame(
            '$: Value must be of type object{statuses?: list<\'active\'|\'banned\'>, items?: list<array{level: 1|2, '
                . 'suit: \'Hearts\'|\'Spades\'}>, ...}, but "x" given',
            self::formatFirstViolation($mapper, OrderStub::class, 'x'),
        );
    }

    public function testFieldWithNameOfNoIdentifierIsQuoted(): void
    {
        $mapper = self::createMapper(config: new Configuration(expectedTypeDepth: 1));

        self::assertSame(
            '$: Value must be of type object{user-name: string, \'first name\': string}, but "x" given',
            self::formatFirstViolation($mapper, RenamedFieldStub::class, 'x'),
        );
    }

    public function testTypeOfExpanderOfUserIsNamedByExpansion(): void
    {
        $mapper = self::createMapper(
            decorators: [new AddressDecoratorStub()],
            expanders: [new OpaqueTypeExpanderStub()],
        );

        self::assertSame(
            '$.address: Value must be of type ?address, but "x" given',
            self::formatFirstViolation($mapper, OrderStub::class, ['address' => 'x']),
        );
    }

    public function testExpanderOfUserIsTriedBeforeDefaultOnes(): void
    {
        $mapper = self::createMapper(expanders: [new ClassExpanderStub()]);

        self::assertSame(
            '$: Value must be of type address, but "x" given',
            self::formatFirstViolation($mapper, AddressStub::class, 'x'),
        );
    }

    public function testFailedExpanderLeavesViolationWithoutType(): void
    {
        $mapper = self::createMapper(expanders: [new FailingExpanderStub()]);

        self::assertSame(
            '$.statuses[0]: Value "x" is invalid',
            self::formatFirstViolation($mapper, OrderStub::class, ['statuses' => ['x']]),
        );
    }

    public function testDecoratedTypeIsExpandedAsItIsBuilt(): void
    {
        $mapper = self::createMapper(
            decorators: [new AddressDecoratorStub()],
            config: new Configuration(expectedTypeDepth: 0),
        );

        self::assertSame(
            '$.address: Value must be of type ?object, but "x" given',
            self::formatFirstViolation($mapper, OrderStub::class, ['address' => 'x']),
        );
    }

    public function testMessageOfHookIsHidden(): void
    {
        $hook = new HookStub(AddressStub::class, beforeDenormalize: static function (): never {
            throw new \DomainException('SQLSTATE[23000]: table "addresses"');
        });

        self::assertSame(
            '$.address: Value array(1) is rejected',
            self::formatFirstViolation(
                self::createMapper(hooks: [$hook]),
                OrderStub::class,
                ['address' => ['city' => 'Paris']],
            ),
        );
    }

    /**
     * @param non-empty-string $type
     */
    private static function formatFirstViolation(Mapper $mapper, string $type, mixed $input): string
    {
        $error = self::captureThrowable(static fn(): mixed => $mapper->denormalize($input, $type));

        self::assertInstanceOf(InvalidValueException::class, $error);

        $violation = $error->violations[0];

        return (new JsonPathPrinter())->print($violation->path) . ': '
            . self::createPublicFormatter()->format($violation);
    }
}
