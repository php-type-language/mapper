<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Exception\TypeNotFoundException;
use TypeLang\Mapper\Type\Attribute\TargetTypeName;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class MixedType implements LogicalTypeInterface
{
    /**
     * @var non-empty-string
     */
    private const DEFAULT_TYPE_NAME = 'mixed';

    /**
     * @param non-empty-string $name
     */
    public function __construct(
        #[TargetTypeName]
        private readonly string $name = self::DEFAULT_TYPE_NAME,
    ) {}

    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        return new NamedTypeNode($this->name);
    }

    /**
     * @throws TypeNotFoundException
     */
    private function getType(mixed $value, RepositoryInterface $types): TypeInterface
    {
        /**
         * @phpstan-ignore-next-line : False-positive, the 'get_debug_type' method returns a non-empty string
         */
        return $types->getByStatement(new NamedTypeNode(\get_debug_type($value)));
    }

    public function supportsCasting(mixed $value, LocalContext $context): bool
    {
        return true;
    }

    /**
     * @throws TypeNotFoundException
     */
    public function cast(mixed $value, RepositoryInterface $types, LocalContext $context): mixed
    {
        $type = $this->getType($value, $types);

        return $type->cast($value, $types, $context);
    }
}
