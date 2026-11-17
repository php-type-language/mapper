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
 * @template-implements TypeBuilderInterface<array<array-key, mixed>>
 */
final class ArrayTypeBuilder implements TypeBuilderInterface
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

        return $this->create($node, $arguments['K'] ?? null, $arguments['V'] ?? null, $context);
    }

    /**
     * @return TypeInterface<array<array-key, mixed>>
     * @throws TypeDefinitionException in case of the key or the value type
     *         cannot be built
     */
    private function create(TypeNode $node, ?TypeNode $key, ?TypeNode $value, BuildContext $context): TypeInterface
    {
        $key ??= new NamedTypeNode(new Name('array-key'));
        $value ??= new NamedTypeNode(new Name('mixed'));

        $name = $context->printer->print($node);

        $keyName = $context->printer->print($key);

        /** @var ArrayType<array-key, mixed> */
        $type = new ArrayType($context->types->getType($key), $context->types->getType($value), $name, $keyName);

        if ($this->checks === []) {
            return $type;
        }

        return new RefinedType($type, $this->checks, $name);
    }
}
