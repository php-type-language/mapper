<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Mapper\Type\SimpleType;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends NamedTypeBuilder<SimpleType>
 */
class SimpleTypeBuilder extends NamedTypeBuilder
{
    /**
     * @param non-empty-array<non-empty-string>|non-empty-string $names
     * @param class-string<SimpleType> $type
     */
    public function __construct(
        array|string $names,
        protected readonly string $type,
    ) {
        parent::__construct($names);
    }

    public function build(TypeStatement $statement, RepositoryInterface $types): SimpleType
    {
        $this->expectNoShapeFields($statement);
        $this->expectNoTemplateArguments($statement);

        return new ($this->type)($statement->name->toString());
    }
}
