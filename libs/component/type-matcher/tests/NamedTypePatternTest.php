<?php

declare(strict_types=1);

namespace TypeLang\Mapper\TypeMatcher\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\TypeMatcher\Exception\InvalidPatternException;
use TypeLang\Mapper\TypeMatcher\NamedTypePattern;
use TypeLang\Parser\TypeParser;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\Template\TypedTemplateArgumentNode;
use TypeLang\Type\TypeNode;

final class NamedTypePatternTest extends TestCase
{
    /**
     * @return iterable<non-empty-string, array{list<non-empty-string>, string, array<non-empty-string, int<0, max>>}>
     */
    public static function matchingDataProvider(): iterable
    {
        yield 'name without arguments' => [['int'], 'int', []];
        yield 'name in other case' => [['int'], 'INT', []];
        yield 'form in other case' => [['Int'], 'int', []];
        yield 'alias' => [['int', 'integer'], 'integer', []];
        yield 'virtual name' => [['non-empty-string'], 'non-empty-string', []];
        yield 'one argument' => [['list<value>'], 'list<string>', ['value' => 0]];
        yield 'two arguments' => [['int<min, max>'], 'int<0, 42>', ['min' => 0, 'max' => 1]];
        yield 'form chosen by number of arguments' => [
            ['array', 'array<value>', 'array<key, value>'],
            'array<string, int>',
            ['key' => 0, 'value' => 1],
        ];
        yield 'arguments in other case' => [['map<k, K>'], 'map<int, string>', ['k' => 0, 'K' => 1]];
        yield 'argument of any type' => [
            ['list<value>'],
            'list<array{a: int}|null>',
            ['value' => 0],
        ];
    }

    /**
     * @param list<non-empty-string> $forms
     * @param array<non-empty-string, int<0, max>> $arguments
     */
    #[DataProvider('matchingDataProvider')]
    public function testTypeMatchesForm(array $forms, string $type, array $arguments): void
    {
        $node = self::parse($type);
        $expected = [];

        foreach ($arguments as $name => $position) {
            $expected[$name] = self::getArgumentValue($node, $position);
        }

        self::assertSame($expected, (new NamedTypePattern($forms))->findArguments($node));
    }

    /**
     * @return iterable<non-empty-string, array{list<non-empty-string>, string}>
     */
    public static function mismatchingDataProvider(): iterable
    {
        yield 'not a named type' => [['int'], 'int|string'];
        yield 'other name' => [['int', 'integer'], 'string'];
        yield 'fully qualified name' => [['int'], '\int'];
        yield 'name with namespace' => [['int'], 'Some\int'];
        yield 'shape fields' => [['array'], 'array{a: int}'];
        yield 'empty shape' => [['array'], 'array{}'];
        yield 'unexpected arguments' => [['int'], 'int<1, 2>'];
        yield 'wrong number of arguments' => [['int', 'int<min, max>'], 'int<1, 2, 3>'];
        yield 'one argument expected' => [['list<value>'], 'list<int, string>'];
        yield 'wildcard argument' => [['list<value>'], 'list<*>'];
        yield 'argument hint' => [['array<key, value>'], 'array<int, covariant string>'];
    }

    /**
     * @param list<non-empty-string> $forms
     */
    #[DataProvider('mismatchingDataProvider')]
    public function testTypeDoesNotMatchForm(array $forms, string $type): void
    {
        self::assertNull((new NamedTypePattern($forms))->findArguments(self::parse($type)));
    }

    /**
     * @return iterable<non-empty-string, array{list<non-empty-string>, non-empty-string}>
     */
    public static function invalidPatternDataProvider(): iterable
    {
        yield 'no forms' => [[], 'Pattern must contain at least one form'];
        yield 'syntax error' => [['int<'], 'Pattern form "int<" is not a valid type'];
        yield 'not a named type' => [['int|string'], 'Pattern form "int|string" must be a name with no namespace'];
        yield 'name with namespace' => [['Some\Any'], 'Pattern form "Some\Any" must be a name with no namespace'];
        yield 'fully qualified name' => [['\int'], 'Pattern form "\int" must be a name with no namespace'];
        yield 'shape fields' => [['array{a: int}'], 'Pattern form "array{a: int}" must not contain shape fields'];
        yield 'argument with arguments' => [
            ['list<list<value>>'],
            'Template argument #1 of pattern form "list<list<value>>" must be a name',
        ];
        yield 'literal argument' => [['int<0, max>'], 'Template argument #1 of pattern form "int<0, max>" must be a name'];
        yield 'argument hint' => [
            ['list<covariant value>'],
            'Template argument #1 of pattern form "list<covariant value>" must be a name',
        ];
        yield 'wildcard argument' => [['list<*>'], 'Template argument #1 of pattern form "list<*>" must be a name'];
        yield 'repeated argument' => [
            ['array<value, value>'],
            'Pattern form "array<value, value>" contains argument "value" more than once',
        ];
        yield 'repeated form' => [
            ['array<key, value>', 'ARRAY<k, v>'],
            'Pattern form "ARRAY<k, v>" repeats the name and the number of arguments',
        ];
    }

    /**
     * @param list<non-empty-string> $forms
     * @param non-empty-string $message
     */
    #[DataProvider('invalidPatternDataProvider')]
    public function testInvalidFormIsRejected(array $forms, string $message): void
    {
        try {
            new NamedTypePattern($forms);
        } catch (InvalidPatternException $e) {
            self::assertStringStartsWith($message, $e->getMessage());

            return;
        }

        self::fail('InvalidPatternException is expected');
    }

    private static function parse(string $type): TypeNode
    {
        $node = (new TypeParser())->parse($type);

        self::assertInstanceOf(TypeNode::class, $node);

        return $node;
    }

    /**
     * @param int<0, max> $position
     */
    private static function getArgumentValue(TypeNode $type, int $position): TypeNode
    {
        self::assertInstanceOf(NamedTypeNode::class, $type);

        $argument = $type->arguments?->children[$position] ?? null;

        self::assertInstanceOf(TypedTemplateArgumentNode::class, $argument);

        return $argument->value;
    }
}
