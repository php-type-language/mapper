<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Type\ArrayType;
use TypeLang\Mapper\Type\Check\CheckInterface;
use TypeLang\Mapper\Type\RefinedType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Mapper\TypeMatcher\Exception\InvalidPatternException;
use TypeLang\Mapper\TypeMatcher\NamedTypePattern;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\TypeNode;

/**
 * Creates the type of `array`, `array<V>` and `array<K, V>`. The key
 * type is `array-key` and the value type is `mixed` unless they are given.
 * With checks the array is refined by them, such as `non-empty-array<K, V>`.
 *
 * @template-implements CompositeTypeBuilderInterface<array<array-key, mixed>>
 */
final class ArrayTypeBuilder implements CompositeTypeBuilderInterface
{
    private readonly NamedTypePattern $pattern;

    /**
     * @param list<CheckInterface> $checks
     * @throws InvalidPatternException in case of the pattern is written wrong
     */
    public function __construct(
        ?NamedTypePattern $pattern = null,
        private readonly array $checks = [],
    ) {
        $this->pattern = $pattern ?? new NamedTypePattern(['array', 'array<V>', 'array<K, V>']);
    }

    /**
     * @return TypeInterface<array<array-key, mixed>>|null
     */
    public function tryBuild(TypeNode $node, BuildContext $context): ?TypeInterface
    {
        $arguments = $this->pattern->findArguments($node);

        if ($arguments === null) {
            return null;
        }

        return $this->create(self::getKeyType($arguments), self::getValueType($arguments), $context);
    }

    /**
     * Returns the type of the values or of the keys at any key.
     */
    public function tryGetNestedType(TypeNode $node, int|string $key, bool $isKey): ?TypeNode
    {
        $arguments = $this->pattern->findArguments($node);

        if ($arguments === null) {
            return null;
        }

        if ($isKey) {
            return self::getKeyType($arguments);
        }

        return self::getValueType($arguments);
    }

    /**
     * @param array<non-empty-string, TypeNode> $arguments
     */
    private static function getKeyType(array $arguments): TypeNode
    {
        return $arguments['K'] ?? new NamedTypeNode(new Name('array-key'));
    }

    /**
     * @param array<non-empty-string, TypeNode> $arguments
     */
    private static function getValueType(array $arguments): TypeNode
    {
        return $arguments['V'] ?? new NamedTypeNode(new Name('mixed'));
    }

    /**
     * @return TypeInterface<array<array-key, mixed>>
     * @throws TypeDefinitionException in case of the key or the value type
     *         cannot be built
     */
    private function create(TypeNode $key, TypeNode $value, BuildContext $context): TypeInterface
    {
        /** @var ArrayType<array-key, mixed> */
        $type = new ArrayType($context->types->getType($key), $context->types->getType($value));

        if ($this->checks === []) {
            return $type;
        }

        return new RefinedType($type, $this->checks);
    }
}
