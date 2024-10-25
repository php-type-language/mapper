<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\Shape\ShapeFieldsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\Hint\TemplateArgumentHintsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\MissingTemplateArgumentsException;
use TypeLang\Mapper\Exception\Definition\Template\TooManyTemplateArgumentsException;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Runtime\Repository\TypeRepository;
use TypeLang\Mapper\Type\NonEmpty;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends NamedTypeBuilder<NonEmpty>
 */
class NonEmptyTypeBuilder extends NamedTypeBuilder
{
    /**
     * @inheritDoc
     *
     * @throws ShapeFieldsNotSupportedException
     * @throws TemplateArgumentHintsNotSupportedException
     * @throws MissingTemplateArgumentsException
     * @throws TooManyTemplateArgumentsException
     * @throws TypeNotFoundException
     * @throws \Throwable
     */
    public function build(TypeStatement $statement, TypeRepository $types): NonEmpty
    {
        $this->expectNoShapeFields($statement);
        $this->expectTemplateArgumentsCount($statement, 1);

        assert($statement->arguments !== null);

        /** @var TemplateArgumentNode $inner */
        $inner = $statement->arguments->first();

        $this->expectNoTemplateArgumentHint($statement, $inner);

        return new NonEmpty(
            type: $types->getByStatement($inner->value),
        );
    }
}
