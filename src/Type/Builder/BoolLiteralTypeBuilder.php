<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\BoolLiteralType;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Parser\Node\Literal\BoolLiteralNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-implements TypeBuilderInterface<BoolLiteralNode, BoolLiteralType>
 */
final class BoolLiteralTypeBuilder implements TypeBuilderInterface
{
    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof BoolLiteralNode;
    }

    public function build(TypeStatement $statement, RepositoryInterface $types): BoolLiteralType
    {
        $name = $statement->raw;

        if ($name === '') {
            $name = $statement->value ? 'true' : 'false';
        }

        return new BoolLiteralType($name, $statement->value);
    }
}
