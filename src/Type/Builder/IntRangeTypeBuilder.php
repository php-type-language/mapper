<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Exception\Definition\Shape\ShapeFieldsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\Hint\TemplateArgumentHintNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\InvalidTemplateArgumentException;
use TypeLang\Mapper\Exception\Definition\Template\OneOfTemplateArgumentsCountException;
use TypeLang\Mapper\Exception\Definition\Template\TooManyTemplateArgumentsInRangeException;
use TypeLang\Mapper\Type\IntRangeType;
use TypeLang\Mapper\Type\IntType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Literal\IntLiteralNode;
use TypeLang\Parser\Node\Literal\StringLiteralNode;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode as ArgNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Node\Stmt\UnionTypeNode;

/**
 * @template-extends NamedTypeBuilder<TypeInterface<int>>
 */
class IntRangeTypeBuilder extends NamedTypeBuilder
{
    /**
     * @throws InvalidTemplateArgumentException
     * @throws TemplateArgumentHintNotSupportedException
     * @throws TooManyTemplateArgumentsInRangeException
     * @throws ShapeFieldsNotSupportedException
     */
    public function build(TypeStatement $statement, BuildingContext $context): TypeInterface
    {
        /** @phpstan-ignore-next-line : Additional DbC assertion */
        assert($statement instanceof NamedTypeNode);

        $this->expectNoShapeFields($statement);

        $arguments = $statement->arguments->items ?? [];

        return match (\count($arguments)) {
            0 => $this->createIntType(),
            2 => $this->createIntRangeType(
                min: $this->fetchTemplateArgumentValue($statement, $arguments[0]),
                max: $this->fetchTemplateArgumentValue($statement, $arguments[1]),
            ),
            default => throw OneOfTemplateArgumentsCountException::becauseArgumentsCountDoesNotMatch(
                variants: [0, 2],
                type: $statement,
            ),
        };
    }

    /**
     * @return TypeInterface<int>
     */
    protected function createIntType(): TypeInterface
    {
        return new IntType();
    }

    /**
     * @return TypeInterface<int>
     * @throws InvalidTemplateArgumentException
     * @throws TemplateArgumentHintNotSupportedException
     */
    protected function createIntRangeType(int $min, int $max): TypeInterface
    {
        return new IntRangeType($min, $max, $this->createIntType());
    }

    /**
     * @throws InvalidTemplateArgumentException
     * @throws TemplateArgumentHintNotSupportedException
     */
    private function fetchTemplateArgumentValue(NamedTypeNode $statement, ArgNode $argument): int
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

        throw InvalidTemplateArgumentException::becauseTemplateArgumentMustBe(
            argument: $argument,
            expected: new UnionTypeNode(
                new NamedTypeNode('int'),
                StringLiteralNode::createFromValue('min'),
                StringLiteralNode::createFromValue('max'),
            ),
            type: $statement,
        );
    }
}
