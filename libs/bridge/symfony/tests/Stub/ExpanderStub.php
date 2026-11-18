<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Tests\Stub;

use TypeLang\Mapper\Bridge\Symfony\Attribute\AsMapperTypeExpander;
use TypeLang\Mapper\Context\ExpandContext;
use TypeLang\Mapper\Type\Expander\TypeExpanderInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\TypeNode;

#[AsMapperTypeExpander]
final class ExpanderStub implements TypeExpanderInterface
{
    public function tryExpand(TypeInterface $type, ExpandContext $context): ?TypeNode
    {
        return null;
    }
}
