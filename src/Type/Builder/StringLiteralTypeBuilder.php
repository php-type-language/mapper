<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Type\StringLiteralType;
use TypeLang\Parser\Node\Literal\StringLiteralNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-implements TypeBuilderInterface<StringLiteralNode, StringLiteralType>
 */
class StringLiteralTypeBuilder implements TypeBuilderInterface
{
    public const DEFAULT_PARENT_TYPE = 'string';

    public function __construct(
        /**
         * @var non-empty-string
         */
        protected readonly string $type = self::DEFAULT_PARENT_TYPE,
    ) {}

    public function isSupported(TypeStatement $stmt): bool
    {
        return $stmt instanceof StringLiteralNode;
    }

    public function build(TypeStatement $stmt, BuildingContext $context): StringLiteralType
    {
        return new StringLiteralType(
            value: $stmt->value,
            type: $context->getTypeByDefinition($this->type),
        );
    }
}
