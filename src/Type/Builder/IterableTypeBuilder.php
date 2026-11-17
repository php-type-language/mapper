<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Type\IterableType;
use TypeLang\Mapper\TypeMatcher\Exception\InvalidPatternException;
use TypeLang\Mapper\TypeMatcher\NamedTypePattern;
use TypeLang\Type\TypeNode;

/**
 * Creates the type of `iterable`, `iterable<V>` and `iterable<K, V>`: the
 * array of the same arguments that also takes a {@see \Traversable}.
 *
 * @template-implements TypeBuilderInterface<array<array-key, mixed>>
 */
final class IterableTypeBuilder implements TypeBuilderInterface
{
    private readonly ArrayTypeBuilder $arrays;

    /**
     * @throws InvalidPatternException in case of the pattern is written wrong
     */
    public function __construct(?NamedTypePattern $pattern = null)
    {
        $this->arrays = new ArrayTypeBuilder(
            $pattern ?? new NamedTypePattern(['iterable', 'iterable<V>', 'iterable<K, V>']),
        );
    }

    /**
     * @return IterableType<array<array-key, mixed>>|null
     */
    public function tryBuild(TypeNode $node, BuildContext $context): ?IterableType
    {
        $array = $this->arrays->tryBuild($node, $context);

        if ($array === null) {
            return null;
        }

        return new IterableType($array);
    }
}
