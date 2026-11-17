<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Mapper\TypeMatcher\NamedTypePattern;
use TypeLang\Type\TypeNode;

/**
 * Returns the same type for any node matching the pattern, such as `int`.
 * Meant for forms without arguments: a form with arguments gets the same
 * type for any of them, a type depending on its arguments needs its own
 * builder over the pattern.
 *
 * ```
 * $pattern = new NamedTypePattern(['int', 'integer']);
 *
 * new NamedTypeBuilder($pattern, new IntType());
 * ```
 *
 * @template T
 *
 * @template-implements TypeBuilderInterface<T>
 */
final class NamedTypeBuilder implements TypeBuilderInterface
{
    public function __construct(
        private readonly NamedTypePattern $pattern,
        /**
         * @var TypeInterface<T>
         */
        private readonly TypeInterface $type,
    ) {}

    /**
     * @return TypeInterface<T>|null
     */
    public function tryBuild(TypeNode $node, BuildContext $context): ?TypeInterface
    {
        if ($this->pattern->findArguments($node) === null) {
            return null;
        }

        return $this->type;
    }
}
