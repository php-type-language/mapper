<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\Shape\ShapeFieldsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\TemplateArgumentsNotSupportedException;
use TypeLang\Mapper\Runtime\Parser\TypeParserRuntimeInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryRuntimeInterface;
use TypeLang\Mapper\Type\IntType;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends NamedTypeBuilder<IntType>
 */
class IntRangeTypeBuilder extends NamedTypeBuilder
{
    /**
     * @param non-empty-array<non-empty-string>|non-empty-string $names
     */
    public function __construct(
        array|string $names,
        protected readonly int $min,
        protected readonly int $max,
    ) {
        parent::__construct($names);
    }

    /**
     * @throws ShapeFieldsNotSupportedException
     * @throws TemplateArgumentsNotSupportedException
     */
    public function build(
        TypeStatement $statement,
        TypeRepositoryRuntimeInterface $types,
        TypeParserRuntimeInterface $parser,
    ): IntType {
        $this->expectNoShapeFields($statement);
        $this->expectNoTemplateArguments($statement);

        return new IntType(
            min: $this->min,
            max: $this->max,
            userDefinedRange: false,
        );
    }
}
