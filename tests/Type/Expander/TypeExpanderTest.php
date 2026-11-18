<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Expander;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Context\ExpandContext;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Exception\TypeExpansionException;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Tests\TestCase;
use TypeLang\Mapper\Type\BoolType;
use TypeLang\Mapper\Type\Expander\TypeExpander;
use TypeLang\Mapper\Type\Expander\TypeExpanderInterface;
use TypeLang\Mapper\Type\Repository\InspectableTypeRepositoryInterface;
use TypeLang\Mapper\Type\StringType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\TypeParser;
use TypeLang\Printer\PrettyTypePrinter;
use TypeLang\Type\Literal\StringLiteralNode;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\Template\TemplateListNode;
use TypeLang\Type\Template\TypedTemplateArgumentNode;
use TypeLang\Type\TypeNode;

final class TypeExpanderTest extends TestCase
{
    public function testTypeWithoutExpandersIsReturnedAsIs(): void
    {
        $type = self::parse('list<Status>|null');

        self::assertSame($type, (new TypeExpander(self::createRepository(), []))->expand($type));
    }

    public function testTypeWithoutExpandedNamesIsReturnedAsIs(): void
    {
        $type = self::parse('list<int|int>|array{a: string}');

        self::assertSame($type, self::createExpander()->expand($type));
    }

    public function testExpansionDoesNotChangeGivenType(): void
    {
        $type = self::parse('list<Status>|null');
        $expanded = self::createExpander()->expand($type);

        self::assertSame("list<'x'>|null", (new PrettyTypePrinter())->print($expanded));
        self::assertSame('list<Status>|null', (new PrettyTypePrinter())->print($type));
    }

    public function testNameThatIsNotTypeIsKept(): void
    {
        $type = self::parse('int<0, max>');

        self::assertSame($type, self::createExpander()->expand($type));
    }

    public function testFailureOfBuildFailsExpansion(): void
    {
        $error = self::captureThrowable(
            static fn(): TypeNode => self::createExpander()->expand(self::parse('list<Secret>')),
        );

        self::assertInstanceOf(TypeExpansionException::class, $error);
    }

    public function testFailureOfExpanderFailsExpansion(): void
    {
        $expander = new class implements TypeExpanderInterface {
            public function tryExpand(TypeInterface $type, ExpandContext $context): ?TypeNode
            {
                throw new \TypeError('Bug of the expander');
            }
        };

        $error = self::captureThrowable(
            static fn(): TypeNode => (new TypeExpander(self::createRepository(), [$expander]))
                ->expand(self::parse('list<Status>')),
        );

        self::assertInstanceOf(TypeExpansionException::class, $error);
        self::assertInstanceOf(\TypeError::class, $error->getPrevious());
    }

    /**
     * @return iterable<non-empty-string, array{non-empty-string, int<0, max>, non-empty-string}>
     */
    public static function wrappedShapeDataProvider(): iterable
    {
        yield 'nullable at depth' => ['?object{a: int, b: int}', 0, '?object'];
        yield 'union at depth' => ['object{a: int, b: int}|null', 0, 'object|null'];
        yield 'list at depth' => ['list<object{a: int, b: int}>', 0, 'list<object>'];
        yield 'nullable above depth' => ['?object{a: int, b: int}', 1, '?object{a: int, ...}'];
    }

    /**
     * @param non-empty-string $expansion
     * @param int<0, max> $maxDepth
     * @param non-empty-string $expected
     */
    #[DataProvider('wrappedShapeDataProvider')]
    public function testLimitsApplyToShapeInsideExpansion(string $expansion, int $maxDepth, string $expected): void
    {
        $node = self::parse($expansion);
        $expander = new class ($node) implements TypeExpanderInterface {
            public function __construct(
                private readonly TypeNode $expansion,
            ) {}

            public function tryExpand(TypeInterface $type, ExpandContext $context): ?TypeNode
            {
                if (!$type instanceof BoolType) {
                    return null;
                }

                return $this->expansion;
            }
        };

        $expanded = (new TypeExpander(self::createRepository(), [$expander], $maxDepth, maxFields: 1))
            ->expand(self::parse('Status'));

        self::assertSame($expected, (new PrettyTypePrinter())->print($expanded));
    }

    public function testTypeNestedBelowMaximumDepthOutOfFieldsIsExpanded(): void
    {
        $expander = new class implements TypeExpanderInterface {
            public function tryExpand(TypeInterface $type, ExpandContext $context): ?TypeNode
            {
                if (!$type instanceof BoolType) {
                    return null;
                }

                return new NamedTypeNode(new Name('list'), new TemplateListNode([
                    new TypedTemplateArgumentNode($context->expand(new NamedTypeNode(new Name('Status')))),
                ]));
            }
        };

        $expanded = (new TypeExpander(self::createRepository(), [$expander]))->expand(self::parse('Status'));

        self::assertSame('list<list<mixed>>', (new PrettyTypePrinter())->print($expanded));
    }

    /**
     * @param non-empty-string $type
     */
    private static function parse(string $type): TypeNode
    {
        return (new TypeParser())->forType()->parse($type);
    }

    /**
     * Expands the type of `Status` into `'x'`.
     */
    private static function createExpander(): TypeExpander
    {
        $expander = new class implements TypeExpanderInterface {
            public function tryExpand(TypeInterface $type, ExpandContext $context): ?TypeNode
            {
                if (!$type instanceof BoolType) {
                    return null;
                }

                return new StringLiteralNode('x');
            }
        };

        return new TypeExpander(self::createRepository(), [$expander]);
    }

    /**
     * Builds `Status` by {@see BoolType}, other names by {@see StringType}.
     */
    private static function createRepository(): InspectableTypeRepositoryInterface
    {
        return new class implements InspectableTypeRepositoryInterface {
            public function getType(TypeNode $node): TypeInterface
            {
                if ($node instanceof NamedTypeNode && $node->name->is('Status')) {
                    return new BoolType();
                }

                if ($node instanceof NamedTypeNode && $node->name->is('max')) {
                    throw TypeDefinitionException::becauseTypeIsNotSupported('max');
                }

                if ($node instanceof NamedTypeNode && $node->name->is('Secret')) {
                    throw TypeDefinitionException::becauseTypeCannotBeBuilt(
                        'Secret',
                        new \RuntimeException('Bug of the builder'),
                    );
                }

                return new StringType();
            }

            public function findUndecoratedType(TypeNode $node): ?TypeInterface
            {
                return null;
            }

            public function findBuilder(TypeNode $node): ?TypeBuilderInterface
            {
                return null;
            }
        };
    }
}
