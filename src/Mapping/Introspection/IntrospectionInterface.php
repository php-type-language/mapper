<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Introspection;

use TypeLang\Mapper\Runtime\Context;
use TypeLang\Parser\Node\Stmt\TypeStatement;

interface IntrospectionInterface
{
    /**
     * Gets an abstract syntax tree (AST) representation of the structure.
     */
    public function getTypeStatement(Context $context): TypeStatement;
}
