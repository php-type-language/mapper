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
class BoolLiteralTypeBuilder implements TypeBuilderInterface
{
    /**
     * @var non-empty-lowercase-string
     */
    public const DEFAULT_TRUE_TYPE_NAME = 'true';

    /**
     * @var non-empty-lowercase-string
     */
    public const DEFAULT_FALSE_TYPE_NAME = 'false';

    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof BoolLiteralNode;
    }

    /**
     * @return non-empty-string
     */
    private function getTypeName(BoolLiteralNode $literal): string
    {
        return match (true) {
            $literal->raw !== '' => $literal->raw,
            $literal->value === true => self::DEFAULT_TRUE_TYPE_NAME,
            default => self::DEFAULT_FALSE_TYPE_NAME,
        };
    }

    public function build(TypeStatement $statement, RepositoryInterface $types): BoolLiteralType
    {
        $name = $this->getTypeName($statement);

        return new BoolLiteralType($name, $statement->value);
    }
}
