<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Parser;

use JetBrains\PhpStorm\Language;
use TypeLang\Parser\Node\Stmt\TypeStatement;

interface TypeParserInterface
{
    /**
     * @param non-empty-string $definition
     * @param \ReflectionClass<object>|null $context Class in which the
     *        definition is declared. Relative names are resolved in relation
     *        to this class, so statements of different contexts can not be shared.
     *
     * @throws \Throwable in case of any internal error occurs
     */
    public function getStatementByDefinition(
        #[Language('PHP')] string $definition,
        ?\ReflectionClass $context = null,
    ): TypeStatement;
}
