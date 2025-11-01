<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader\Stub;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class DummyParser implements TypeParserInterface
{
    public function getStatementByDefinition(#[Language('PHP')] string $definition): TypeStatement
    {
        return new NamedTypeNode($definition);
    }
}
