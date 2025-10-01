<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver\DocBlockDriver\Reader;

use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Parser\Node\Stmt\TypeStatement;

interface TagReaderInterface
{
    public function findType(\ReflectionProperty $property, PropertyMetadata $meta): ?TypeStatement;
}
