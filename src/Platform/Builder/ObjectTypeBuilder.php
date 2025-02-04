<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Builder;

use TypeLang\Mapper\Platform\Type\ObjectType;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends NamedTypeBuilder<ObjectType>
 */
class ObjectTypeBuilder extends NamedTypeBuilder
{
    public function build(
        TypeStatement $statement,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): ObjectType {
        $this->expectNoShapeFields($statement);
        $this->expectNoTemplateArguments($statement);

        return new ObjectType();
    }
}
