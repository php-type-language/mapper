<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

class MixedType implements TypeInterface
{
    /**
     * @var non-empty-lowercase-string
     */
    public const DEFAULT_TYPE_NAME = 'mixed';

    /**
     * @param non-empty-string $name
     */
    public function __construct(
        private readonly string $name = self::DEFAULT_TYPE_NAME,
    ) {}

    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        return new NamedTypeNode($this->name);
    }

    public function match(mixed $value, LocalContext $context): bool
    {
        return true;
    }

    /**
     * @throws TypeNotFoundException
     */
    public function cast(mixed $value, LocalContext $context): mixed
    {
        return $context->getTypes()
            ->getByValue($value)
            ->cast($value, $context);
    }
}
