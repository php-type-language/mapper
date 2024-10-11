<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\NamedType;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends NamedTypeBuilder<NamedType>
 */
class SimpleTypeBuilder extends NamedTypeBuilder
{
    /**
     * @param non-empty-array<non-empty-string>|non-empty-string $names
     * @param class-string<NamedType> $type
     */
    public function __construct(
        array|string $names,
        protected readonly string $type,
    ) {
        parent::__construct($names);
    }

    public function build(TypeStatement $statement, RepositoryInterface $types): NamedType
    {
        $this->expectNoShapeFields($statement);
        $this->expectNoTemplateArguments($statement);

        return new ($this->type)($statement->name->toString());
    }
}
