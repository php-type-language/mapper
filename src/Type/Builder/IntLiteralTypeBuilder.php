<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\IntLiteralType;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Parser\Node\Literal\IntLiteralNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-implements TypeBuilderInterface<IntLiteralNode, IntLiteralType>
 */
class IntLiteralTypeBuilder implements TypeBuilderInterface
{
    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof IntLiteralNode;
    }

    public function build(TypeStatement $statement, RepositoryInterface $types): IntLiteralType
    {
        /** @var numeric-string $name */
        $name = $statement->raw;

        return new IntLiteralType($name, $statement->value);
    }
}
