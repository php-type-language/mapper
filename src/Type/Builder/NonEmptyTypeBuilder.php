<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\NonEmpty;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends NamedTypeBuilder<NonEmpty>
 */
class NonEmptyTypeBuilder extends NamedTypeBuilder
{
    public function build(TypeStatement $statement, RepositoryInterface $types): NonEmpty
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
