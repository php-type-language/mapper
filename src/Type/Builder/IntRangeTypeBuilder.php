<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\Shape\ShapeFieldsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\Hint\TemplateArgumentHintsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\InvalidTemplateArgumentException;
use TypeLang\Mapper\Exception\Definition\Template\TooManyTemplateArgumentsException;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\IntRangeType;
use TypeLang\Mapper\Type\IntType;
use TypeLang\Parser\Node\Literal\IntLiteralNode;
use TypeLang\Parser\Node\Literal\StringLiteralNode;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode as ArgNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Node\Stmt\UnionTypeNode;

/**
 * @template-extends NamedTypeBuilder<IntRangeType|IntType>
 */
class IntRangeTypeBuilder extends NamedTypeBuilder
{
    /**
     * @throws InvalidTemplateArgumentException
     * @throws TemplateArgumentHintsNotSupportedException
     * @throws TooManyTemplateArgumentsException
     * @throws ShapeFieldsNotSupportedException
     */
    public function build(
        TypeStatement $statement,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): IntRangeType|IntType {
        /** @phpstan-ignore-next-line : Additional DbC assertion */
        assert($statement instanceof NamedTypeNode);

        $this->expectNoShapeFields($statement);

        $arguments = $statement->arguments->items ?? [];

        return match (\count($arguments)) {
            0 => new IntType(),
            1 => $this->buildWithMinValue($statement, $arguments[0]),
            2 => $this->buildWithMinMaxValues($statement, $arguments[0], $arguments[1]),
            default => throw TooManyTemplateArgumentsException::becauseTemplateArgumentsRangeOverflows(
                minSupportedArgumentsCount: 0,
                maxSupportedArgumentsCount: 2,
                type: $statement,
            ),
        };
    }

    /**
     * @throws InvalidTemplateArgumentException
     * @throws TemplateArgumentHintsNotSupportedException
     */
    private function buildWithMinValue(NamedTypeNode $statement, ArgNode $min): IntRangeType
    {
        $value = $this->fetchTemplateArgumentValue($statement, $min);

        return new IntRangeType($value);
    }

    /**
     * @throws InvalidTemplateArgumentException
     * @throws TemplateArgumentHintsNotSupportedException
     */
    private function buildWithMinMaxValues(NamedTypeNode $statement, ArgNode $min, ArgNode $max): IntRangeType
    {
        $from = $this->fetchTemplateArgumentValue($statement, $min);
        $to = $this->fetchTemplateArgumentValue($statement, $max);

        return new IntRangeType($from, $to);
    }

    /**
     * @throws InvalidTemplateArgumentException
     * @throws TemplateArgumentHintsNotSupportedException
     */
    private function fetchTemplateArgumentValue(TypeStatement $statement, ArgNode $argument): int
    {
        $this->expectNoTemplateArgumentHint($statement, $argument);

        $value = $argument->value;

        if ($value instanceof IntLiteralNode) {
            return $value->value;
        }

        if ($value instanceof NamedTypeNode) {
            switch ($value->name->toLowerString()) {
                case 'min':
                    return IntRangeType::DEFAULT_INT_MIN;
                case 'max':
                    return IntRangeType::DEFAULT_INT_MAX;
            }
        }

        throw InvalidTemplateArgumentException::becauseTemplateArgumentIsInvalid(
            expected: new UnionTypeNode(
                new NamedTypeNode('int'),
                StringLiteralNode::createFromValue('min'),
                StringLiteralNode::createFromValue('max'),
            ),
            argument: $argument,
            type: $statement,
        );
    }
}
