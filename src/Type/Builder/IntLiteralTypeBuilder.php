<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Type\IntLiteralType;
use TypeLang\Parser\Node\Literal\IntLiteralNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-implements TypeBuilderInterface<IntLiteralNode, IntLiteralType>
 */
class IntLiteralTypeBuilder implements TypeBuilderInterface
{
    public const DEFAULT_PARENT_TYPE = 'int';

    public function __construct(
        /**
         * @var non-empty-string
         */
        protected readonly string $type = self::DEFAULT_PARENT_TYPE,
    ) {}

    public function isSupported(TypeStatement $stmt): bool
    {
        return $stmt instanceof IntLiteralNode;
    }

    public function build(TypeStatement $stmt, BuildingContext $context): IntLiteralType
    {
        return new IntLiteralType(
            value: $stmt->value,
            type: $context->getTypeByDefinition($this->type),
        );
    }
}
