<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\Shape\ShapeFieldsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\TemplateArgumentsNotSupportedException;
use TypeLang\Mapper\Runtime\Repository\TypeRepository;
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
     * @inheritDoc
     *
     * @throws ShapeFieldsNotSupportedException
     * @throws TemplateArgumentsNotSupportedException
     */
    public function build(TypeStatement $statement, TypeRepository $types): IntType
    {
        $this->expectNoShapeFields($statement);
        $this->expectNoTemplateArguments($statement);

        return new IntType(
            min: $this->min,
            max: $this->max,
            userDefinedRange: false,
        );
    }
}
