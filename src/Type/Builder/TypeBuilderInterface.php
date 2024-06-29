<?php

declare(strict_types=1);

namespace Serafim\Mapper\Type\Builder;

use Serafim\Mapper\Registry\RegistryInterface;
use Serafim\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

interface TypeBuilderInterface
{
    public function isSupported(TypeStatement $statement): bool;

    public function build(TypeStatement $type, RegistryInterface $context): TypeInterface;
}
