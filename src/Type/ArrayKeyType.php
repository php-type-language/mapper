<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

class ArrayKeyType extends UnionType
{
    /**
     * @var non-empty-string
     */
    public const DEFAULT_TYPE_NAME = 'array-key';

    /**
     * @param non-empty-string $name
     */
    public function __construct(
        public readonly string $name = self::DEFAULT_TYPE_NAME,
    ) {
        parent::__construct([
            new IntType(),
            new StringType(),
        ]);
    }

    #[\Override]
    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        return new NamedTypeNode($this->name);
    }
}
