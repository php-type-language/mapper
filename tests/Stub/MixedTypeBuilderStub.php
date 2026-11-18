<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\MixedType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\TypeNode;

/**
 * Builds any node as `mixed`, the last builder of a repository of a test.
 *
 * @template-implements TypeBuilderInterface<mixed>
 */
final class MixedTypeBuilderStub implements TypeBuilderInterface
{
    public function tryBuild(TypeNode $node, BuildContext $context): TypeInterface
    {
        return new MixedType();
    }
}
