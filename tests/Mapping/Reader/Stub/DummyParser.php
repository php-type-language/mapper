<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader\Stub;

use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class DummyParser implements TypeParserInterface
{
    public function getStatementByDefinition(string $definition): TypeStatement
    {
        return new NamedTypeNode($definition);
    }
}
