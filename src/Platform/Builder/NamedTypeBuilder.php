<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Builder;

use TypeLang\Mapper\Platform\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-covariant TType of TypeInterface
 * @template-extends Builder<NamedTypeNode, TType>
 */
abstract class NamedTypeBuilder extends Builder
{
    /**
     * @var non-empty-list<non-empty-lowercase-string>
     */
    protected readonly array $lower;

    /**
     * @param non-empty-array<non-empty-string>|non-empty-string $names
     */
    public function __construct(array|string $names)
    {
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
}
