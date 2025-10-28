<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;
use TypeLang\Mapper\Type\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\ScalarType;
use TypeLang\Mapper\Type\Specifier\TypeSpecifierInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template TType of scalar = scalar
 * @template TTypeInstance of ScalarType = ScalarType
 *
 * @template-extends NamedTypeBuilder<TTypeInstance>
 */
class ScalarTypeBuilder extends NamedTypeBuilder
{
    public function __construct(
        array|string $name,
        /**
         * @var class-string<TTypeInstance>
         */
        protected readonly string $class,
        /**
         * @var TypeCoercerInterface<TType>
         */
        protected readonly TypeCoercerInterface $coercer,
        /**
         * @var TypeSpecifierInterface<TType>|null
         */
        protected readonly ?TypeSpecifierInterface $specifier = null,
    ) {
        parent::__construct($name);
    }

    public function build(
        TypeStatement $statement,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): ScalarType {
        \assert($statement instanceof NamedTypeNode);

        $this->expectNoShapeFields($statement);
        $this->expectNoTemplateArguments($statement);

        return new ($this->class)(
            coercer: $this->coercer,
            specifier: $this->specifier,
        );
    }
}
