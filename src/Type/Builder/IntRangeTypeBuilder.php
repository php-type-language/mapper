<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Type\Check\IntRangeCheck;
use TypeLang\Mapper\Type\IntType;
use TypeLang\Mapper\Type\RefinedType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Mapper\TypeMatcher\Exception\InvalidPatternException;
use TypeLang\Mapper\TypeMatcher\NamedTypePattern;
use TypeLang\Type\Literal\IntLiteralNode;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\TypeNode;

/**
 * Creates the type of `int<min, max>`: each bound is an integer or the name
 * `min` or `max` of an open bound. A range open on both sides is `int`.
 *
 * @template-implements TypeBuilderInterface<int>
 */
final class IntRangeTypeBuilder implements TypeBuilderInterface
{
    private readonly NamedTypePattern $pattern;

    /**
     * @throws InvalidPatternException in case of the pattern is written wrong
     */
    public function __construct(
        ?NamedTypePattern $pattern = null,
        /**
         * Type the range refines
         *
         * @var TypeInterface<int>
         */
        private readonly TypeInterface $type = new IntType(),
    ) {
        $this->pattern = $pattern ?? new NamedTypePattern(['int<min, max>']);
    }

    /**
     * @return TypeInterface<int>|null
     * @throws TypeDefinitionException in case of a bound is not an integer,
     *         `min` or `max`, or the lower bound is greater than the upper one
     */
    public function tryBuild(TypeNode $node, BuildContext $context): ?TypeInterface
    {
        $arguments = $this->pattern->findArguments($node);

        if ($arguments === null) {
            return null;
        }

        $name = $context->printer->print($node);

        $min = self::readBound($name, 1, $arguments['min'], 'min');
        $max = self::readBound($name, 2, $arguments['max'], 'max');

        if ($min === null && $max === null) {
            return $this->type;
        }

        if ($min !== null && $max !== null && $min > $max) {
            throw TypeDefinitionException::becauseRangeIsEmpty($name);
        }

        return new RefinedType($this->type, [new IntRangeCheck($min, $max)]);
    }

    /**
     * Returns {@see null} in case of the bound is open.
     *
     * @param non-empty-string $type
     * @param int<1, 2> $position
     * @param 'min'|'max' $open name of the open bound at the position
     * @throws TypeDefinitionException in case of the bound is not an integer
     *         or the name of the open bound
     */
    private static function readBound(string $type, int $position, TypeNode $bound, string $open): ?int
    {
        if ($bound instanceof IntLiteralNode) {
            return $bound->value;
        }

        if (
            $bound instanceof NamedTypeNode
            && $bound->arguments === null
            && $bound->fields === null
            && $bound->name->is($open)
        ) {
            return null;
        }

        throw TypeDefinitionException::becauseRangeBoundIsInvalid($type, $position, $open);
    }
}
