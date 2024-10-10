<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Mapper\Type\SimpleType;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends Builder<NamedTypeNode, SimpleType>
 */
class SimpleTypeBuilder extends Builder
{
    /**
     * @var non-empty-list<non-empty-lowercase-string>
     */
    private readonly array $lower;

    /**
     * @param non-empty-array<non-empty-string>|non-empty-string $names
     * @param class-string<SimpleType> $type
     */
    public function __construct(
        array|string $names,
        protected readonly string $type,
    ) {
        $this->lower = $this->formatNames($names);
    }

    /**
     * @param non-empty-array<non-empty-string>|non-empty-string $names
     *
     * @return non-empty-list<non-empty-lowercase-string>
     */
    private function formatNames(array|string $names): array
    {
        $result = [];

        foreach (\is_string($names) ? [$names] : $names as $name) {
            $result[] = \strtolower($name);
        }

        return $result;
    }

    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof NamedTypeNode
            && \in_array($statement->name->toLowerString(), $this->lower, true);
    }

    public function build(TypeStatement $statement, RepositoryInterface $types): SimpleType
    {
        $this->expectNoShapeFields($statement);
        $this->expectNoTemplateArguments($statement);

        return new ($this->type)($statement->name->toString());
    }
}
