<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class TypeMetadata extends Metadata
{
    public function __construct(
        /**
         * Gets type reference.
         */
        public readonly TypeInterface $type,
        /**
         * Gets declarative type representation.
         */
        public readonly TypeStatement $statement,
        ?int $createdAt = null,
    ) {
        parent::__construct($createdAt);
    }
}
