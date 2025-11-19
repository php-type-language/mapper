<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Type\BoolLiteralType;
use TypeLang\Parser\Node\Literal\BoolLiteralNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-implements TypeBuilderInterface<BoolLiteralNode, BoolLiteralType>
 */
class BoolLiteralTypeBuilder implements TypeBuilderInterface
{
    public const DEFAULT_PARENT_TYPE = 'bool';

    public function __construct(
        /**
         * @var non-empty-string
         */
        protected readonly string $type = self::DEFAULT_PARENT_TYPE,
    ) {}

    public function isSupported(TypeStatement $stmt): bool
    {
        return $stmt instanceof BoolLiteralNode;
    }

    public function build(TypeStatement $stmt, BuildingContext $context): BoolLiteralType
    {
        return new BoolLiteralType(
            value: $stmt->value,
            type: $context->getTypeByDefinition($this->type),
        );
    }
}
