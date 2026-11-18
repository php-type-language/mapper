<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Type\Check\CheckInterface;
use TypeLang\Mapper\Type\ListType;
use TypeLang\Mapper\Type\RefinedType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Mapper\TypeMatcher\Exception\InvalidPatternException;
use TypeLang\Mapper\TypeMatcher\NamedTypePattern;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\TypeNode;

/**
 * Creates the type of `list` and `list<T>`, the element type of `list` is
 * `mixed`. With checks the list is refined by them, such as
 * `non-empty-list<T>`.
 *
 * ```
 * new ListTypeBuilder(
 *     new NamedTypePattern(['non-empty-list', 'non-empty-list<T>']),
 *     [new NonEmptyCheck()],
 * );
 * ```
 *
 * @template-implements CompositeTypeBuilderInterface<list<mixed>>
 */
final class ListTypeBuilder implements CompositeTypeBuilderInterface
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
        $this->pattern = $pattern ?? new NamedTypePattern(['list', 'list<T>']);
    }

    /**
     * @return TypeInterface<list<mixed>>|null
     */
    public function tryBuild(TypeNode $node, BuildContext $context): ?TypeInterface
    {
        $arguments = $this->pattern->findArguments($node);

        if ($arguments === null) {
            return null;
        }

        $type = new ListType($context->types->getType($this->getItemType($arguments)));

        if ($this->checks === []) {
            return $type;
        }

        return new RefinedType($type, $this->checks);
    }

    /**
     * Returns the type of the elements at any key, {@see null} for a key:
     * the keys of a list are its positions.
     */
    public function tryGetNestedType(TypeNode $node, int|string $key, bool $isKey): ?TypeNode
    {
        $arguments = $this->pattern->findArguments($node);

        if ($arguments === null || $isKey) {
            return null;
        }

        return $this->getItemType($arguments);
    }

    /**
     * @param array<non-empty-string, TypeNode> $arguments
     */
    private function getItemType(array $arguments): TypeNode
    {
        return $arguments['T'] ?? new NamedTypeNode(new Name('mixed'));
    }
}
