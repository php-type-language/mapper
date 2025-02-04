<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

use TypeLang\Mapper\Platform\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class TypeMetadata extends Metadata
{
    public function __construct(
        private readonly TypeInterface $type,
        private readonly TypeStatement $statement,
        ?int $createdAt = null,
    ) {
        parent::__construct($createdAt);
    }

    public function getType(): TypeInterface
    {
        return $this->type;
    }

    public function getTypeStatement(): TypeStatement
    {
        return $this->statement;
    }
}
