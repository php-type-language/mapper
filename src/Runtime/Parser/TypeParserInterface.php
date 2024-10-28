<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Parser;

use JetBrains\PhpStorm\Language;
use TypeLang\Parser\Node\Stmt\TypeStatement;

interface TypeParserInterface
{
    /**
     * TODO in the future specific local exception types for the parse errors
     *      should be added.
     *
     * @param non-empty-string $definition
     *
     * @throws \Throwable in case of any internal error occurs
     */
    public function getStatementByDefinition(#[Language('PHP')] string $definition): TypeStatement;
}
