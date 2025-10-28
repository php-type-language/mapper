<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\Shape\ShapeFieldsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\TemplateArgumentsNotSupportedException;
use TypeLang\Mapper\Type\MixedType;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;
use TypeLang\Mapper\Type\Repository\TypeRepositoryInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends NamedTypeBuilder<MixedType>
 */
class MixedTypeBuilder extends NamedTypeBuilder
{
    /**
     * @throws ShapeFieldsNotSupportedException
     * @throws TemplateArgumentsNotSupportedException
     */
    public function build(
        TypeStatement $statement,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): MixedType {
        /** @phpstan-ignore-next-line : Additional DbC assertion */
        assert($statement instanceof NamedTypeNode);

        $this->expectNoShapeFields($statement);
        $this->expectNoTemplateArguments($statement);

        return new MixedType();
    }
}
