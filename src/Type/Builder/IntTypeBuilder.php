<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\Template\Hint\TemplateArgumentHintsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\InvalidTemplateArgumentException;
use TypeLang\Mapper\Exception\Definition\Template\TooManyTemplateArgumentsException;
use TypeLang\Mapper\Runtime\Repository\TypeRepository;
use TypeLang\Mapper\Type\IntType;
use TypeLang\Parser\Node\Literal\IntLiteralNode;
use TypeLang\Parser\Node\Literal\StringLiteralNode;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode as ArgNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Node\Stmt\UnionTypeNode;

/**
 * @template-extends NamedTypeBuilder<IntType>
 */
class IntTypeBuilder extends NamedTypeBuilder
{
    public function build(TypeStatement $statement, TypeRepository $types): IntType
    {
        $this->expectNoShapeFields($statement);

        $arguments = $statement->arguments->items ?? [];

        return match (\count($arguments)) {
            0 => new IntType(),
            1 => $this->buildWithMinValue($statement, $arguments[0]),
            2 => $this->buildWithMinMaxValues($statement, $arguments[0], $arguments[1]),
            default => throw TooManyTemplateArgumentsException::becauseTemplateArgumentsRangeOverflows(
                passedArgumentsCount: \count($arguments),
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
    private function buildWithMinValue(NamedTypeNode $statement, ArgNode $min): IntType
    {
        $value = $this->fetchTemplateArgumentValue($statement, $min);

        return new IntType($value);
    }

    /**
     * @throws InvalidTemplateArgumentException
     * @throws TemplateArgumentHintsNotSupportedException
     */
    private function buildWithMinMaxValues(NamedTypeNode $statement, ArgNode $min, ArgNode $max): IntType
    {
        $from = $this->fetchTemplateArgumentValue($statement, $min);
        $to = $this->fetchTemplateArgumentValue($statement, $max);

        return new IntType($from, $to);
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
                    return IntType::DEFAULT_INT_MIN;
                case 'max':
                    return IntType::DEFAULT_INT_MAX;
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
