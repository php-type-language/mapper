<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\BoolType;
use TypeLang\Mapper\Type\MixedType;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends Builder<NamedTypeNode, MixedType>
 */
final class MixedTypeBuilder extends Builder
{
    /**
     * @var non-empty-lowercase-string
     */
    private readonly string $lower;

    /**
     * @param non-empty-string $name
     */
    public function __construct(string $name = MixedType::DEFAULT_TYPE_NAME)
    {
        $this->lower = \strtolower($name);
    }

    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof NamedTypeNode
            && $statement->name->toLowerString() === $this->lower;
    }

    public function build(TypeStatement $statement, RepositoryInterface $types): MixedType
    {
        self::assertNoTemplateArguments($statement);
        self::assertNoShapeFields($statement);

        return new MixedType($statement->name->toString());
    }
}
