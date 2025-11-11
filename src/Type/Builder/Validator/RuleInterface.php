<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder\Validator;

use TypeLang\Mapper\Exception\Definition\DefinitionException;
use TypeLang\Parser\Node\Stmt\TypeStatement;

interface RuleInterface
{
    /**
     * @return iterable<array-key, DefinitionException>
     */
    public function validate(TypeStatement $stmt): iterable;
}
