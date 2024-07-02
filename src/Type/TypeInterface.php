<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Registry\RegistryInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

interface TypeInterface
{
    public function getTypeStatement(LocalContext $context): TypeStatement;

    public function cast(mixed $value, RegistryInterface $types, LocalContext $context): mixed;
}
