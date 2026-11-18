<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Type\Builder\CompositeTypeBuilderInterface;
use TypeLang\Mapper\Type\MixedType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Mapper\TypeMatcher\NamedTypePattern;
use TypeLang\Type\TypeNode;

/**
 * Builds `Collection<T>` of the user as `mixed` and tells the type of its
 * elements.
 *
 * @template-implements CompositeTypeBuilderInterface<mixed>
 */
final class CompositeTypeBuilderStub implements CompositeTypeBuilderInterface
{
    private readonly NamedTypePattern $pattern;

    public function __construct()
    {
        $this->pattern = new NamedTypePattern(['Collection<T>']);
    }

    public function tryBuild(TypeNode $node, BuildContext $context): ?TypeInterface
    {
        if ($this->pattern->findArguments($node) === null) {
            return null;
        }

        return new MixedType();
    }

    public function tryGetNestedType(TypeNode $node, int|string $key, bool $isKey): ?TypeNode
    {
        if ($isKey) {
            return null;
        }

        return $this->pattern->findArguments($node)['T'] ?? null;
    }
}
