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
 * @template-implements TypeBuilderInterface<list<mixed>>
 */
final class ListTypeBuilder implements TypeBuilderInterface
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

        $item = $arguments['T'] ?? new NamedTypeNode(new Name('mixed'));

        $name = $context->printer->print($node);

        $type = new ListType($context->types->getType($item), $name);

        if ($this->checks === []) {
            return $type;
        }

        return new RefinedType($type, $this->checks, $name);
    }
}
