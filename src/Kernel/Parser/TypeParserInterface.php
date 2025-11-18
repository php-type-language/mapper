<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Parser;

use JetBrains\PhpStorm\Language;
use TypeLang\Parser\Node\Stmt\TypeStatement;

interface TypeParserInterface
{
    /**
     * @param non-empty-string $definition
     *
     * @throws \Throwable in case of any internal error occurs
     */
    public function getStatementByDefinition(#[Language('PHP')] string $definition): TypeStatement;
}
