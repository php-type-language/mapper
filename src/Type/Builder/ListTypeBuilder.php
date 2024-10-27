<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\Shape\ShapeFieldsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\Hint\TemplateArgumentHintsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\TooManyTemplateArgumentsException;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\ListType;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends NamedTypeBuilder<ListType>
 */
class ListTypeBuilder extends NamedTypeBuilder
{
    /**
     * @throws ShapeFieldsNotSupportedException
     * @throws TemplateArgumentHintsNotSupportedException
     * @throws TooManyTemplateArgumentsException
     * @throws TypeNotFoundException
     * @throws \Throwable
     */
    public function build(
        TypeStatement $statement,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): ListType {
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
