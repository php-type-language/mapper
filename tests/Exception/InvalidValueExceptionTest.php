<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Exception;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Diagnostics\Path\Segment\KeySegment;
use TypeLang\Mapper\Diagnostics\Path\Segment\PropertySegment;
use TypeLang\Mapper\Diagnostics\Printer\Path\JsonPathPrinter;
use TypeLang\Mapper\Diagnostics\ViolationFormatter;
use TypeLang\Mapper\Exception\InvalidValueException;
use TypeLang\Mapper\Exception\InvalidValueException\DeclaredTypeFinder;
use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Context\ClassAccessors;
use TypeLang\Mapper\Instantiator\InstantiatorFactory;
use TypeLang\Mapper\Mapping\MetadataBuilder;
use TypeLang\Mapper\PropertyAccessor\PropertyAccessorFactory;
use TypeLang\Mapper\Tests\Stub\CompositeTypeBuilderStub;
use TypeLang\Mapper\Tests\Stub\MixedTypeBuilderStub;
use TypeLang\Mapper\Tests\TestCase;
use TypeLang\Mapper\Type\Builder\ArrayTypeBuilder;
use TypeLang\Mapper\Type\Builder\IterableTypeBuilder;
use TypeLang\Mapper\Type\Builder\ListTypeBuilder;
use TypeLang\Mapper\Type\Builder\ShapeTypeBuilder;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\Builder\TypesListTypeBuilder;
use TypeLang\Mapper\Type\MixedType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Mapper\Type\Repository\TypeRepository;
use TypeLang\Mapper\TypeMatcher\NamedTypePattern;
use TypeLang\Parser\TypeParser;
use TypeLang\Printer\PrettyTypePrinter;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\TypeNode;

final class InvalidValueExceptionTest extends TestCase
{
    public function testUnexpectedFieldNameIsCutAndValidUtf8(): void
    {
        $long = InvalidValueException::becauseFieldIsUnexpected(
            \str_repeat('a', 100),
            new KeySegment('x'),
            1,
        );
        $broken = InvalidValueException::becauseFieldIsUnexpected("bad\xffname", new KeySegment('x'), 1);
        $mixed = InvalidValueException::becauseFieldIsUnexpected("ok \u{444} \xd0", new KeySegment('x'), 1);
        $multibyte = InvalidValueException::becauseFieldIsUnexpected(
            \str_repeat("\u{444}", 65),
            new KeySegment('x'),
            1,
        );
        $exact = InvalidValueException::becauseFieldIsUnexpected(\str_repeat("\u{444}", 64), new KeySegment('x'), 1);
        $huge = InvalidValueException::becauseFieldIsUnexpected(\str_repeat('a', 1_000_000), new KeySegment('x'), 1);

        $formatter = self::createFormatter();

        self::assertSame(
            'Unexpected field "' . \str_repeat('a', 64) . '..."',
            $formatter->format($long->violations[0]),
        );
        self::assertSame('Unexpected field "bad\xFFname"', $formatter->format($broken->violations[0]));
        self::assertSame('Unexpected field "ok \xD1\x84 \xD0"', $formatter->format($mixed->violations[0]));
        self::assertSame(
            'Unexpected field "' . \str_repeat("\u{444}", 64) . '..."',
            $formatter->format($multibyte->violations[0]),
        );
        self::assertSame(
            'Unexpected field "' . \str_repeat("\u{444}", 64) . '"',
            $formatter->format($exact->violations[0]),
        );
        self::assertSame(
            'Unexpected field "' . \str_repeat('a', 64) . '..."',
            $formatter->format($huge->violations[0]),
        );

        foreach ([$long, $broken, $mixed, $multibyte, $exact, $huge] as $error) {
            self::assertNotSame('', \json_encode($error->getMessage(), \JSON_THROW_ON_ERROR));
            self::assertNotSame('', \json_encode($formatter->format($error->violations[0]), \JSON_THROW_ON_ERROR));
        }
    }

    public function testWrappedErrorIsNotHeldByTraceOfNewOne(): void
    {
        $inner = InvalidValueException::becauseInvalidType('x');
        $outer = InvalidValueException::wrapWithPath($inner, new KeySegment(0), 'x', type: self::parse('int'));

        foreach ($outer->getTrace() as $frame) {
            foreach ($frame['args'] ?? [] as $argument) {
                self::assertNotSame($inner, $argument);
            }
        }

        self::assertSame('$[0]: Value must be of type int, but "x" given', self::formatDeclared($outer));
    }

    public function testTemplateReplacesMessagesOfValueItselfOnly(): void
    {
        $error = InvalidValueException::wrapWithPath(
            InvalidValueException::wrapWithPath(
                InvalidValueException::becauseInvalidType('x'),
                new KeySegment('nested'),
                'x',
            ),
            new KeySegment('outer'),
            [],
            'Outer is invalid',
        );

        self::assertSame('$.outer.nested: Value "x" is invalid', $error->getMessage());

        $error = InvalidValueException::wrapWithPath(
            InvalidValueException::becauseInvalidType('x'),
            new KeySegment('outer'),
            'x',
            'Outer is invalid',
        );

        self::assertSame('$.outer: Outer is invalid', $error->getMessage());
    }

    public function testCollectsUntilLimitAndKeepsSingleErrorAsIs(): void
    {
        $violations = [];
        $first = InvalidValueException::becauseInvalidType('a');

        InvalidValueException::collect($violations, $first, 3);
        InvalidValueException::collect($violations, InvalidValueException::becauseInvalidType('b'), 3);

        self::assertCount(2, $violations);

        $error = self::captureThrowable(static function () use (&$violations): void {
            InvalidValueException::collect($violations, InvalidValueException::becauseInvalidType('c'), 3);
        });

        self::assertInstanceOf(InvalidValueException::class, $error);
        self::assertCount(3, $error->violations);

        $single = [];

        self::assertSame($first, self::captureThrowable(static function () use (&$single, $first): void {
            InvalidValueException::collect($single, $first, 1);
        }));
    }

    /**
     * @return iterable<non-empty-string, array{non-empty-string, list<int|string>, string}>
     */
    public static function declaredTypeDataProvider(): iterable
    {
        yield 'type itself' => ['?int', [], '?int'];
        yield 'element of list' => ['list<int>', [3], 'int'];
        yield 'element of non-empty list' => ['non-empty-list<int>', [0], 'int'];
        yield 'element of types list' => ['int[]', [0], 'int'];
        yield 'value of array' => ['array<string, int>', ['a'], 'int'];
        yield 'value of array without key' => ['array<int>', ['a'], 'int'];
        yield 'value of iterable' => ['iterable<string, int>', ['a'], 'int'];
        yield 'field of shape' => ['array{id: int, name: string}', ['name'], 'string'];
        yield 'implicit field of shape' => ['array{int, string}', [1], 'string'];
        yield 'numeric field of shape' => ['array{"1": int}', [1], 'int'];
        yield 'field of object shape' => ['object{id: int}', ['id'], 'int'];
        yield 'through nullable' => ['?list<int>', [0], 'int'];
        yield 'through union with null' => ['list<int>|null', [0], 'int'];
        yield 'element of collection of builder' => ['Collection<int>', [0], 'int'];
        yield 'deep' => ['list<array{items: array<string, ?float>}>', [0, 'items', 'x'], '?float'];
    }

    /**
     * @param non-empty-string $type
     * @param list<int|string> $keys
     */
    #[DataProvider('declaredTypeDataProvider')]
    public function testDeclaredTypeOfNestedValueIsFoundByPath(string $type, array $keys, string $expected): void
    {
        $error = InvalidValueException::becauseInvalidType('x');

        foreach (\array_reverse($keys) as $key) {
            $error = InvalidValueException::wrapWithPath($error, new KeySegment($key), 'x');
        }

        $error = $error->withDeclaredType(self::parse($type), self::createFinder());

        self::assertStringContainsString(\sprintf('must be of type %s', $expected), self::formatDeclared($error));
    }

    /**
     * @return iterable<non-empty-string, array{non-empty-string, list<int|string>}>
     */
    public static function unknownDeclaredTypeDataProvider(): iterable
    {
        yield 'class' => ['Some\Any', ['id']];
        yield 'generic type without builder' => ['Other<int>', [0]];
        yield 'fields of name without builder' => ['Set{weak: true}', ['weak']];
        yield 'union' => ['list<int>|array<string, int>', [0]];
        yield 'missing field of shape' => ['array{id: int}', ['name']];
        yield 'wildcard argument' => ['list<*>', [0]];
    }

    /**
     * @param non-empty-string $type
     * @param list<int|string> $keys
     */
    #[DataProvider('unknownDeclaredTypeDataProvider')]
    public function testPathThroughUnknownTypeLeavesViolationUntyped(string $type, array $keys): void
    {
        $error = InvalidValueException::becauseInvalidType('x');

        foreach (\array_reverse($keys) as $key) {
            $error = InvalidValueException::wrapWithPath($error, new KeySegment($key), 'x');
        }

        $error = $error->withDeclaredType(self::parse($type), self::createFinder());

        self::assertNull($error->violations[0]->declared);
        self::assertStringEndsWith('Value "x" is invalid', $error->getMessage());
    }

    public function testTypeOverriddenByBuilderOfUserHasNoDeclaredTypeOfElements(): void
    {
        $builder = new class implements TypeBuilderInterface {
            public function tryBuild(TypeNode $node, BuildContext $context): ?TypeInterface
            {
                if (!$node instanceof NamedTypeNode || !$node->name->is('array')) {
                    return null;
                }

                return new MixedType();
            }
        };

        $error = InvalidValueException::wrapWithPath(
            InvalidValueException::becauseInvalidType('x'),
            new KeySegment(0),
            'x',
        );
        $error = $error->withDeclaredType(self::parse('array<string>'), self::createFinder([$builder]));

        self::assertNull($error->violations[0]->declared);
    }

    public function testKeyOfArrayGivesTypeOfKey(): void
    {
        $error = InvalidValueException::wrapWithPath(
            InvalidValueException::becauseInvalidKey('a'),
            new PropertySegment('map'),
            [],
            type: self::parse('array<int, string>'),
            finder: self::createFinder(),
        );

        self::assertSame('$.map.a: Key must be of type int, but "a" given', self::formatDeclared($error));
    }

    public function testInnerDeclaredTypeIsKept(): void
    {
        $error = InvalidValueException::wrapWithPath(
            InvalidValueException::becauseInvalidType('x', self::parse('int')),
            new KeySegment(0),
            'x',
            type: self::parse('list<string>'),
        );

        self::assertSame('$[0]: Value must be of type int, but "x" given', self::formatDeclared($error));
    }

    public function testMessageOfErrorOfAnotherKindIsHiddenAndKeptAsCause(): void
    {
        $cause = new \DomainException('SQLSTATE[23000]: table "users"');

        $error = InvalidValueException::wrapWithPath($cause, new PropertySegment('email'), 'a@b.c');

        self::assertSame('$.email: Value "a@b.c" is rejected', $error->getMessage());
        self::assertSame($cause, $error->violations[0]->cause);
        self::assertSame($cause, $error->getPrevious());
    }

    public function testCollectedViolationsKeepCauseOfFirstOne(): void
    {
        $cause = new \DomainException('a');
        $violations = [];

        InvalidValueException::collect($violations, InvalidValueException::becauseInvalidType('x'), 10);
        InvalidValueException::collect($violations, InvalidValueException::becauseValueIsRejected($cause, 'y'), 10);

        self::assertSame($cause, InvalidValueException::becauseViolationsAreCollected($violations)->getPrevious());
    }

    /**
     * Renders the first violation for the developer: with the declared type.
     */
    private static function formatDeclared(InvalidValueException $error): string
    {
        $violation = $error->violations[0];

        return (new JsonPathPrinter())->print($violation->path) . ': '
            . self::createFormatter(shouldPrintDeclaredType: true)->format($violation);
    }

    /**
     * @param non-empty-string $type
     */
    private static function parse(string $type): TypeNode
    {
        $node = (new TypeParser())->forType()->parse($type);

        \assert($node instanceof TypeNode);

        return $node;
    }

    /**
     * Creates the finder over the built-in builders of composite types, a
     * builder of `Collection<T>` of the user and a builder of other types.
     *
     * @param list<TypeBuilderInterface<mixed>> $builders builders tried before
     *        the other ones
     */
    private static function createFinder(array $builders = []): DeclaredTypeFinder
    {
        return new DeclaredTypeFinder(new TypeRepository(
            builders: [
                ...$builders,
                new ListTypeBuilder(),
                new ListTypeBuilder(new NamedTypePattern(['non-empty-list', 'non-empty-list<T>'])),
                new ArrayTypeBuilder(),
                new IterableTypeBuilder(),
                new TypesListTypeBuilder(),
                new ShapeTypeBuilder(),
                new CompositeTypeBuilderStub(),
                new MixedTypeBuilderStub(),
            ],
            metadata: MetadataBuilder::createDefault()->build(),
            decorators: [],
            accessors: new ClassAccessors(
                properties: PropertyAccessorFactory::createDefault(),
                instantiators: InstantiatorFactory::createDefault(),
                hooks: [],
            ),
            printer: new PrettyTypePrinter(),
        ));
    }
}
