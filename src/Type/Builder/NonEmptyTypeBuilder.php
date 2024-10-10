<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\NonEmpty;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends Builder<NamedTypeNode, NonEmpty>
 */
class NonEmptyTypeBuilder extends Builder
{
    /**
     * @var non-empty-lowercase-string
     */
    protected readonly string $lower;

    /**
     * @param non-empty-string $name
     */
    public function __construct(string $name = NonEmpty::DEFAULT_TYPE_NAME)
    {
        $this->lower = \strtolower($name);
    }

    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof NamedTypeNode
            && $statement->name->toLowerString() === $this->lower;
    }

    public function build(TypeStatement $statement, RepositoryInterface $types): NonEmpty
    {
        $this->expectNoShapeFields($statement);
        $this->expectTemplateArgumentsCount($statement, 1);

        assert($statement->arguments !== null);

        /** @var TemplateArgumentNode $inner */
        $inner = $statement->arguments->first();

        return new NonEmpty(
            type: $types->getByStatement($inner->value),
            name: $statement->name->toString(),
        );
    }
}
