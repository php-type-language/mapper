<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\Shape\ShapeFieldsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\TemplateArgumentsNotSupportedException;
use TypeLang\Mapper\Runtime\Repository\TypeRepository;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends NamedTypeBuilder<TypeInterface>
 */
class SimpleTypeBuilder extends NamedTypeBuilder
{
    /**
     * @param non-empty-array<non-empty-string>|non-empty-string $names
     * @param class-string<TypeInterface> $type
     */
    public function __construct(
        array|string $names,
        protected readonly string $type,
    ) {
        parent::__construct($names);
    }

    /**
     * @inheritDoc
     *
     * @throws ShapeFieldsNotSupportedException
     * @throws TemplateArgumentsNotSupportedException
     */
    public function build(TypeStatement $statement, TypeRepository $types): TypeInterface
    {
        $this->expectNoShapeFields($statement);
        $this->expectNoTemplateArguments($statement);

        return new ($this->type)();
    }
}
