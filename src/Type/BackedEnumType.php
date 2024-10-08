<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Type\Attribute\TargetTypeName;
use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Parser\Node\Literal\IntLiteralNode;
use TypeLang\Parser\Node\Literal\StringLiteralNode;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Node\Stmt\UnionTypeNode;

final class BackedEnumType extends AsymmetricLogicalType
{
    /**
     * @param class-string<\BackedEnum> $name
     */
    public function __construct(
        #[TargetTypeName]
        private readonly string $name,
    ) {}

    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        if ($context->isNormalization()) {
            return new NamedTypeNode($this->name);
        }

        $cases = [];

        foreach ($this->name::cases() as $case) {
            $cases[] = \is_string($case->value)
                ? new StringLiteralNode(
                    value: $case->value,
                    raw: \sprintf('"%s"', \addcslashes($case->value, '"')),
                )
                : new IntLiteralNode($case->value);
        }

        return match (\count($cases)) {
            0 => new NamedTypeNode('never'),
            1 => $cases[0],
            default => new UnionTypeNode(...$cases),
        };
    }

    protected function supportsNormalization(mixed $value, LocalContext $context): bool
    {
        return $value instanceof \BackedEnum;
    }

    /**
     * Converts enum case (of {@see \BackedEnum}) objects to their
     * scalar representation.
     *
     * @throws InvalidValueException
     */
    public function normalize(mixed $value, RepositoryInterface $types, LocalContext $context): int|string
    {
        if (!$value instanceof \BackedEnum) {
            throw InvalidValueException::becauseInvalidValueGiven(
                value: $value,
                expected: $this->getTypeStatement($context),
                context: $context,
            );
        }

        return $value->value;
    }

    protected function supportsDenormalization(mixed $value, LocalContext $context): bool
    {
        if (!\is_int($value) && !\is_string($value)) {
            return false;
        }

        try {
            $this->name::from($value);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Converts a scalar representation of an enum to an enum case object.
     *
     * @throws InvalidValueException
     */
    public function denormalize(mixed $value, RepositoryInterface $types, LocalContext $context): \BackedEnum
    {
        if (!\is_string($value) && !\is_int($value)) {
            throw InvalidValueException::becauseInvalidValueGiven(
                value: $value,
                expected: $this->getTypeStatement($context),
                context: $context,
            );
        }

        try {
            $case = $this->name::tryFrom($value);
        } catch (\TypeError) {
            throw InvalidValueException::becauseInvalidValueGiven(
                value: $value,
                expected: $this->getTypeStatement($context),
                context: $context,
            );
        }

        return $case ?? throw InvalidValueException::becauseInvalidValueGiven(
            value: $value,
            expected: $this->getTypeStatement($context),
            context: $context,
        );
    }
}
