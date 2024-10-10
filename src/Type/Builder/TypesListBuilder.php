<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\ArrayType;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Parser\Node\Stmt\TypesListNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * Creates a {@see ArrayType} from the "Type[]" syntax.
 *
 * @template-implements TypeBuilderInterface<TypesListNode<TypeStatement>, ArrayType>
 */
final class TypesListBuilder implements TypeBuilderInterface
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(
        private readonly string $name = ArrayType::DEFAULT_TYPE_NAME,
    ) {}

    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof TypesListNode;
    }

    public function build(TypeStatement $statement, RepositoryInterface $types): ArrayType
    {
        $type = $types->getByStatement($statement->type);

        return new ArrayType($this->name, $type);
    }
}
