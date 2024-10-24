<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Runtime\Repository\TypeRepository;
use TypeLang\Mapper\Type\ListType;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends NamedTypeBuilder<ListType>
 */
class ListTypeBuilder extends NamedTypeBuilder
{
    public function build(TypeStatement $statement, TypeRepository $types): ListType
    {
        if ($statement->arguments === null || $statement->arguments->count() === 0) {
            return new ListType();
        }

        $this->expectNoShapeFields($statement);
        $this->expectTemplateArgumentsLessOrEqualThan($statement, 1, 0);

        // The "arguments" has already been checked for non-null
        assert($statement->arguments !== null);

        /** @var TemplateArgumentNode $first */
        $first = $statement->arguments->first();

        if ($first->hint !== null) {
            $this->expectNoTemplateArgumentHint($statement, $first);
        }

        return new ListType(
            type: $types->getByStatement($first->value),
        );
    }
}
