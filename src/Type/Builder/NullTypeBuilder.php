<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Type\NullType;
use TypeLang\Type\Literal\NullLiteralNode;
use TypeLang\Type\TypeNode;

/**
 * Creates the type of `null`.
 *
 * @template-implements TypeBuilderInterface<null>
 */
final class NullTypeBuilder implements TypeBuilderInterface
{
    public function tryBuild(TypeNode $node, BuildContext $context): ?NullType
    {
        if ($node instanceof NullLiteralNode) {
            return new NullType();
        }

        return null;
    }
}
