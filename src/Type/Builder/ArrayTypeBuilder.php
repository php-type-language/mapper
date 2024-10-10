<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\Template\TooManyTemplateArgumentsException;
use TypeLang\Mapper\Type\ArrayKeyType;
use TypeLang\Mapper\Type\ArrayType;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends Builder<NamedTypeNode, ArrayType>
 */
final class ArrayTypeBuilder extends Builder
{
    /**
     * @var non-empty-lowercase-string
     */
    protected readonly string $lower;

    /**
     * @param non-empty-string $name
     */
    public function __construct(string $name = ArrayType::DEFAULT_TYPE_NAME)
    {
        $this->lower = \strtolower($name);
    }

    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof NamedTypeNode
            && $statement->name->toLowerString() === $this->lower;
    }

    public function build(TypeStatement $statement, RepositoryInterface $types): ArrayType
    {
        $this->expectNoShapeFields($statement);

        $arguments = $statement->arguments->items ?? [];

        return match (\count($arguments)) {
            0 => new ArrayType($statement->name->toString()),
            1 => new ArrayType(
                name: $statement->name->toString(),
                key: new ArrayKeyType(),
                value: $types->getByStatement($arguments[0]->value),
            ),
            2 => new ArrayType(
                name: $statement->name->toString(),
                key: $types->getByStatement($arguments[0]->value),
                value: $types->getByStatement($arguments[1]->value),
            ),
            default => throw TooManyTemplateArgumentsException::becauseTemplateArgumentsRangeOverflows(
                passedArgumentsCount: \count($arguments),
                minSupportedArgumentsCount: 0,
                maxSupportedArgumentsCount: 2,
                type: $statement,
            ),
        };
    }
}
