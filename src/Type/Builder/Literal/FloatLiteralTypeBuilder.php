<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder\Literal;

use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\Coercer\FloatTypeCoercer;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Type\LiteralType;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;
use TypeLang\Mapper\Type\Repository\TypeRepositoryInterface;
use TypeLang\Parser\Node\Literal\FloatLiteralNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-implements TypeBuilderInterface<FloatLiteralNode, LiteralType<float>>
 */
class FloatLiteralTypeBuilder implements TypeBuilderInterface
{
    public function __construct(
        /**
         * @var TypeCoercerInterface<float>
         */
        protected readonly TypeCoercerInterface $coercer = new FloatTypeCoercer(),
    ) {}

    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof FloatLiteralNode;
    }

    public function build(
        TypeStatement $statement,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): LiteralType {
        return new LiteralType(
            value: $statement->value,
            coercer: $this->coercer,
        );
    }
}
