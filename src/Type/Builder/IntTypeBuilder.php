<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\Template\Hint\TemplateArgumentHintsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\InvalidTemplateArgumentException;
use TypeLang\Mapper\Exception\Definition\Template\TooManyTemplateArgumentsException;
use TypeLang\Mapper\Type\IntType;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Parser\Node\Literal\IntLiteralNode;
use TypeLang\Parser\Node\Literal\StringLiteralNode;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Node\Stmt\UnionTypeNode;

/**
 * @template-extends Builder<NamedTypeNode, IntType>
 */
class IntTypeBuilder extends Builder
{
    /**
     * @var non-empty-lowercase-string
     */
    protected readonly string $lower;

    /**
     * @param non-empty-string $name
     */
    public function __construct(string $name = IntType::DEFAULT_TYPE_NAME)
    {
        $this->lower = \strtolower($name);
    }

    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof NamedTypeNode
            && $statement->name->toLowerString() === $this->lower;
    }

    public function build(TypeStatement $statement, RepositoryInterface $types): IntType
    {
        $this->expectNoShapeFields($statement);

        $arguments = $statement->arguments->items ?? [];

        return match (\count($arguments)) {
            0 => new IntType($statement->name->toString()),
            1 => new IntType(
                name: $statement->name->toString(),
                min: $this->fetchTemplateArgumentValue($statement, $arguments[0]),
            ),
            2 => new IntType(
                name: $statement->name->toString(),
                min: $this->fetchTemplateArgumentValue($statement, $arguments[0]),
                max: $this->fetchTemplateArgumentValue($statement, $arguments[1]),
            ),
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
    private function fetchTemplateArgumentValue(TypeStatement $statement, TemplateArgumentNode $argument): int
    {
        $this->expectNoTemplateArgumentHint($statement, $argument);

        $value = $argument->value;

        if ($value instanceof IntLiteralNode) {
            return $value->value;
        }

        if ($value instanceof NamedTypeNode) {
            switch ($value->name->toLowerString()) {
                case 'min':
                    return \PHP_INT_MIN;
                case 'max':
                    return \PHP_INT_MAX;
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
