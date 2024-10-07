<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

interface TypeBuilderInterface
{
    public function isSupported(TypeStatement $statement): bool;

    public function build(TypeStatement $type, RepositoryInterface $context): TypeInterface;
}
