<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Registry\RegistryInterface;
use TypeLang\Mapper\Type\Attribute\TargetTypeName;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class NullType implements LogicalTypeInterface
{
    /**
     * @var non-empty-string
     */
    private const DEFAULT_TYPE_NAME = 'null';

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

    public function supportsCasting(mixed $value, LocalContext $context): bool
    {
        return $value === null;
    }

    /**
     * @throws InvalidValueException
     */
    public function cast(mixed $value, RegistryInterface $types, LocalContext $context): mixed
    {
        if (!$context->isStrictTypesEnabled()) {
            return null;
        }

        if ($value !== null) {
            throw InvalidValueException::becauseInvalidValueGiven(
                context: $context,
                expectedType: $this->getTypeStatement($context),
                actualValue: $value,
            );
        }

        return null;
    }
}
