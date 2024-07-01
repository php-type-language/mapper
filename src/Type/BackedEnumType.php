<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Registry\RegistryInterface;
use TypeLang\Mapper\Type\Attribute\TargetTypeName;
use TypeLang\Parser\Node\Literal\IntLiteralNode;
use TypeLang\Parser\Node\Literal\StringLiteralNode;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Node\Stmt\UnionTypeNode;

final class BackedEnumType extends AsymmetricType
{
    /**
     * @param class-string<\BackedEnum> $name
     */
    public function __construct(
        #[TargetTypeName]
        private readonly string $name,
    ) {}

    private function getExpectedTypeStatement(): TypeStatement
    {
        $cases = [];

        foreach ($this->name::cases() as $case) {
            $cases[] = \is_string($case->value)
                ? new StringLiteralNode($case->value)
                : new IntLiteralNode($case->value);
        }

        return match (\count($cases)) {
            0 => new NamedTypeNode('never'),
            1 => $cases[0],
            default => new UnionTypeNode(...$cases),
        };
    }

    /**
     * Converts enum case (of {@see \BackedEnum}) objects to their
     * scalar representation.
     *
     * @throws InvalidValueException
     */
    public function normalize(mixed $value, RegistryInterface $types, LocalContext $context): int|string
    {
        if (!$value instanceof \BackedEnum) {
            throw InvalidValueException::becauseInvalidValueGiven(
                context: $context,
                expectedType: $this->getExpectedTypeStatement(),
                actualValue: $value,
            );
        }

        return $value->value;
    }

    /**
     * Converts a scalar representation of an enum to an enum case object.
     *
     * @throws InvalidValueException
     */
    public function denormalize(mixed $value, RegistryInterface $types, LocalContext $context): \BackedEnum
    {
        if (!\is_string($value) && !\is_int($value)) {
            throw InvalidValueException::becauseInvalidValueGiven(
                context: $context,
                expectedType: $this->getExpectedTypeStatement(),
                actualValue: $value,
            );
        }

        try {
            $case = $this->name::tryFrom($value);
        } catch (\TypeError) {
            throw InvalidValueException::becauseInvalidValueGiven(
                context: $context,
                expectedType: $this->getExpectedTypeStatement(),
                actualValue: $value,
            );
        }

        return $case ?? throw InvalidValueException::becauseInvalidValueGiven(
            context: $context,
            expectedType: $this->getExpectedTypeStatement(),
            actualValue: $value,
        );
    }
}
